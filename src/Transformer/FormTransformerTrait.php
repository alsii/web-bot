<?php
/** @noinspection PhpUndefinedClassConstantInspection */

namespace Alsi\WebBot\Transformer;

use DateTime;
use Exception;
use Alsi\WebBot\Form\FormInterface;

trait FormTransformerTrait
{
    protected function transformToForm($data, FormInterface $form): FormInterface
    {
        foreach (self::OPTIONS as $option) {
            if (array_key_exists('test', $option) && !self::checkCondition($option['test'], $form, $data)) {
                continue;
            }

            if (array_key_exists('path', $option)) {
                self::transformPathOption($data, $form, $option);
            } elseif (array_key_exists('const', $option)) {
                self::transformConstOption($form, $option);
            } else {
                throw new TransformationException(
                    'Wrong option provided: neither PATH, nor CONST',
                    TransformationException::CODE_WRONG_OPTION
                );
            }
        }

        return $form;
    }

    public static function getFromPath($data, $path)
    {
        if (empty($path)) {
            return $data;
        }

        $head = array_shift($path);
        if (!array_key_exists($head, $data)) {
            $strTail = implode('/', $path);

            throw new TransformationException(
                "Path [.../[$head]/$strTail] does not exists",
                TransformationException::CODE_PATH_NOT_EXISTS
            );
        }

        return self::getFromPath($data[$head], $path);
    }

    /**
     * @param $data
     * @param FormInterface $form
     * @param $option
     * @noinspection NotOptimalIfConditionsInspection
     */
    private static function transformPathOption($data, FormInterface $form, $option): void
    {
        $values = self::getFromPath($data, $option['path']);
        $values = array_key_exists('processor', $option) ? $option['processor']($values) : $values;
        if (is_array($values)) {
            if (array_key_exists('map', $option)) {
                $results = [];
                foreach ($values as $key => $value) {
                    $results[$option['map'][$key]['field']][] = $option['map'][$key]['value'];
                }
                foreach ($results as $key => $result) {
                    $form->setField($key, $result);
                }
            } else {
                foreach ($values as $key => $value) {
                    $form->setField($key, $value);
                }
            }
        } elseif (array_key_exists('map', $option)) {
            if (!array_key_exists((is_bool($values) ? (int) $values : $values), $option['map'])) {
                throw new TransformationException(
                    "Key $values does not exists in map option {$option['name']}",
                    TransformationException::CODE_UNKNOWN_MAP_KEY
                );
            }
            $opt = $option['map'][$values];
            if ($opt !== null) {
                $form->setField($opt['field'], $opt['value']);
            }
        } elseif (array_key_exists('field', $option)) {
            $form->setField($option['field'], $values);
        }
    }

    /**
     * @param string $date
     * @return string
     * @throws Exception
     */
    public static function dateProcessor($date): string
    {
        return (new DateTime($date))->format('d.m.Y');
    }

    /**
     * @param array $array
     * @return int
     */
    public static function count_array_elements($array): int
    {
        return count($array);
    }

    /**
     * @param array $array
     * @return int
     */
    protected static function count_array_elements_plus_one($array): int
    {
        return count($array) + 1;
    }

    /**
     * @param $value
     * @return mixed|null
     */
    public static function extractHausnummer($value)
    {
        $result = preg_match('/^\d+/', trim($value), $matches);

        if ($result === 1) {
            return $matches[0];
        }

        return null;
    }

    /**
     * @param $value
     * @return string|null
     */
    public static function extractHausnummerZusatz($value): ?string
    {
        $result = preg_match('/^\d+(.*)$/', trim($value), $matches);

        if ($result === 1) {
            return trim($matches[1]);
        }

        return null;
    }

    public function getSelectors(): array
    {
        return [];
    }

    /**
     * @param FormInterface $form
     * @param array $option
     */
    private static function transformConstOption(FormInterface $form, $option): void
    {
        $form->setField($option['const']['field'], $option['const']['value']);
    }

    /**
     * @param $cond
     * @param FormInterface $form
     * @param array $data
     * @return mixed
     */
    private static function checkCondition($cond, $form, $data)
    {
        if (array_key_exists('processor', $cond) && is_callable($cond['processor'])) {
            return $cond['processor']($cond, $form, $data);
        }

        if (array_key_exists('field', $cond)) {
            $formData = $form->getData();
            $field = $cond['field'];

            return array_key_exists('value', $cond)
                ? !empty($formData[$field]) && $formData[$field] === $cond['value']
                : !empty($formData[$field]);
        }

        if (array_key_exists('path', $cond)) {
            return self::getFromPath($data, $cond['path']) === $cond['value'];
        }

        return true;
    }
}