<?php
/** @noinspection PhpUndefinedClassConstantInspection */

namespace Alsi\WebBot\Form;

/**
 * Trait FormTrait
 * @package Alsi\WebBot\Form
 */
trait FormTrait
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param $key
     * @param $data
     * @return FormInterface
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    public function setField($key, $data): FormInterface
    {
        if (!in_array($key, static::FIELDS, false)) {
            throw new FormException(
                "Can not add [$key] field. This field is not exist in " . get_class($this),
                FormException::CODE_WRONG_FIELD
            );
        }

        $this->data[$key] = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getHtmlFormData(): string
    {
        return self::buildHtmlFormData($this->data);
    }

    /**
     * @param array $data
     * @return string
     */
    public static function buildHtmlFormData($data): string
    {
        $vars = [];
        foreach ($data as $id => $it) {
            if (is_array($it)) {
                foreach ($it as $i) {
                    $vars[] = self::htmlFormVar($id, $i);
                }
            } else {
                $vars[] = self::htmlFormVar($id, $it);
            }
        }

        return implode('&', $vars);
    }

    public function validate(): bool
    {
        return true;
    }

    /**
     * @param $name
     * @param $value
     * @return string
     */
    private static function htmlFormVar($name, $value): string
    {
        return urlencode($name) . '=' . urlencode($value);
    }
}