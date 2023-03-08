<?php
/** @noinspection PhpUndefinedClassConstantInspection */

namespace Alsi\WebBot\Transformer;

use Alsi\WebBot\Form\FormException;
use DateTime;
use Exception;
use Alsi\WebBot\Form\FormInterface;

trait FormTransformerTrait
{
    /** @var FormInterface */
    protected $form;

    protected function transformToForm($data, FormInterface $form, bool $useCode=false): FormInterface
    {
        foreach (self::OPTIONS as $option) {
            if (array_key_exists('test', $option) && !self::checkCondition($option['test'], $form, $data, $useCode)) {
                continue;
            }

            if (array_key_exists('path', $option)) {
                self::transformPathOption($data, $form, $option, $useCode);
            } elseif (array_key_exists('const', $option)) {
                self::transformConstOption($form, $option, $useCode);
            } else {
                throw new TransformationException(
                    'Wrong option provided: neither PATH, nor CONST',
                    TransformationException::CODE_WRONG_OPTION
                );
            }
        }

        return $form;
    }

    /**
     * @param $data
     * @param array $path
     * @param bool $hasDefault
     * @param null $default
     *
     * @throws TransformationException
     * @return mixed
     */
    public static function getFromPath($data, array $path, bool $hasDefault = false, $default=null)
    {
        if (empty($path)) {
            return $data;
        }

        $head = array_shift($path);
        if (!array_key_exists($head, $data)) {
            if ($hasDefault) {
                return $default;
            }

            $strTail = implode('/', $path);
            throw new TransformationException(
                "Path [.../[$head]/$strTail] does not exists",
                TransformationException::CODE_PATH_NOT_EXISTS
            );
        }

        return self::getFromPath($data[$head], $path, $hasDefault, $default);
    }

    private static function transformPathOption(array $data, FormInterface $form, array $options, bool $useFieldCode=false): void
    {
        $values = array_key_exists('default', $options)
            ? self::getFromPath($data, $options['path'], true, $options['default'])
            : self::getFromPath($data, $options['path']);

        $values = array_key_exists('processor', $options) ? $options['processor']($values) : $values;

        if (is_array($values)) {
            if (array_key_exists('map', $options)) {
                self::transformArrayAsMap($values, $options['map'], $form, $useFieldCode);
            } else {
                foreach ($values as $key => $value) {
                    $form->setField($key, $value);
                }
            }
        } elseif (array_key_exists('map', $options)) {
            if (!array_key_exists((is_bool($values) ? (int) $values : $values), $options['map'])) {
                throw new TransformationException(
                    "Key $values does not exists in map option {$options['name']}",
                    TransformationException::CODE_UNKNOWN_MAP_KEY
                );
            }
            $opt = $options['map'][$values];
            if ($opt !== null) {
                $useValueCode = array_key_exists('code', $opt);
                $form->setField($opt['field'], $opt[$useValueCode ? 'code': 'value'], $useFieldCode, $useValueCode);
            }
        } elseif (array_key_exists('field', $options)) {
            $useValueCode = $options['value-decode'] ?? false;
            $form->setField($options['field'], $values, $useFieldCode, $useValueCode);
        }
    }

    /**
     * @throws Exception
     */
    public static function dateProcessor(string $date): string
    {
        return (new DateTime($date))->format('d.m.Y');
    }

    /**
     * @param array $array
     * @return int
     */
    public static function count_array_elements(array $array): int
    {
        return count($array);
    }

    /**
     * @param array $array
     * @return int
     */
    protected static function count_array_elements_plus_one(array $array): int
    {
        return count($array) + 1;
    }

    /**
     * @param $value
     * @return string|null
     */
    public static function extractHausnummer($value): ?string
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
     * @param bool $useCode
     */
    private static function transformConstOption(FormInterface $form, array $option, bool $useCode=false): void
    {
        $const = $option['const'];
        $codeConditionExists = array_key_exists('code', $const);
        $form->setField(
            $const['field'],
            $const[$codeConditionExists ? 'code' : 'value'],
            $useCode,
            $codeConditionExists,
            $const['add'] ?? false,
        );
    }

    /**
     * @param array $cond
     * @param FormInterface $form
     * @param array $data
     * @param bool $useCode
     * @return mixed
     */
    private static function checkCondition(array $cond, FormInterface $form, array $data, bool $useCode=false)
    {
        if (array_key_exists('processor', $cond) && is_callable($cond['processor'])) {
            return $cond['processor']($cond, $form, $data);
        }

        $codeConditionExists = array_key_exists('code', $cond);
        $valueConditionExists = array_key_exists('value', $cond);

        $strictCheck = $cond['strict'] ?? false;

        if (array_key_exists('field', $cond)) {
            $field = $cond['field'];

            try {
                $formValue = $form->getField($field, $useCode, $codeConditionExists);
            } catch (FormException $e) {
                if (($valueConditionExists || $codeConditionExists) && $strictCheck) {
                    $formClass = get_class($form);

                    throw new TransformationException(
                        "The field $field is not set in the form $formClass",
                        TransformationException::CODE_FORM_FIELD_IS_NOT_SET,
                        $e
                    );
                }
                 return false;
            }

            if (!$valueConditionExists && !$codeConditionExists) {
                return true;
            }

            $condValue = $codeConditionExists ? $form->decodeValue($cond['code']) : $cond['value'];
            $operator = $cond['op'] ?? 'same';

            return self::compareValues($condValue, $formValue, $operator);
        }

        if (array_key_exists('path', $cond)) {
            try {
                $dataValue = self::getFromPath($data, $cond['path']);
            } catch (TransformationException $e) {
                if ($valueConditionExists && $strictCheck) {
                    throw $e;
                }

                return false;
            }

            if (!$valueConditionExists && !$codeConditionExists) {
                return true;
            }

            $condValue = $codeConditionExists ? $form->decodeValue($cond['code']) : $cond['value'];
            $operator = $cond['op'] ?? 'same';

            return self::compareValues($condValue, $dataValue, $operator);
        }

        return true;
    }

    /**
     * @param $condValue
     * @param $formValue
     * @param $operator
     * @return bool
     */
    private static function compareValues($condValue, $formValue, $operator): bool
    {
        if (is_array($condValue)) {
            return in_array($formValue, $condValue);
        }

        switch ($operator) {
            case 'same':
                return $formValue === $condValue;
            case 'not-same':
                return $formValue !== $condValue;
            case 'eq':
                /** @noinspection TypeUnsafeComparisonInspection */
                return $formValue == $condValue;
            case 'neq':
                /** @noinspection TypeUnsafeComparisonInspection */
                return $formValue != $condValue;
            case 'lt':
                return $formValue < $condValue;
            case 'le':
                return $formValue <= $condValue;
            case 'ge':
                return $formValue >= $condValue;
            case 'gt':
                return $formValue > $condValue;
            case 'instr':
                return strpos($formValue, (string)$condValue) !== false;
            case 'not-instr':
                return strpos($formValue, (string)$condValue) === false;
        }

        throw new TransformationException("Wrong comparison operator specified: $operator", TransformationException::CODE_WRONG_COMPARISON );
    }

    private static function transformArrayAsMap(array $values, array $map, FormInterface $form, bool $useFieldCode): void
    {
        $results = [];
        foreach ($values as $key => $value) {
            $useValueCode = array_key_exists('code', $map[$key]);
            $results[$map[$key]['field']]['value'][] = $map[$key][$useValueCode ? 'code' : 'value'];
            $results[$map[$key]['field']]['useValueCode'] = $useValueCode;
        }
        foreach ($results as $key => $result) {
            $form->setField($key, $result['value'], $useFieldCode, $result['useValueCode']);
        }
    }

    /**
     * @param FormInterface|null $form
     * @return FormTransformerInterface
     */
    public function setForm(FormInterface $form): FormTransformerInterface
    {
        $this->form = $form;

        return $this;
    }

    public function getForm(): ?FormInterface
    {
        return $this->form;
    }
}