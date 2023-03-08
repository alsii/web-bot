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
    protected $data = [];

    /**
     * @var array
     */
    protected $fieldCodes = [];

    /**
     * @var array
     */
    protected $valueCodes = [];

    /**
     * @param $field
     * @param $value
     * @param bool $fieldIsCode
     * @param bool $valueIsCode
     * @return FormInterface
     */
    public function setField($field, $value, bool $fieldIsCode=false, bool $valueIsCode=false, $addToField=false): FormInterface
    {
        if ($fieldIsCode) {
            $field = $this->decodeField($field);
        }

        if ($valueIsCode) {
            $value = $this->decodeValue($value);
        }

        if (!in_array($field, static::FIELDS)) {
            throw new FormException(
                "Can not add [$field] field. This field is not exist in " . get_class($this),
                FormException::CODE_WRONG_FIELD
            );
        }

        if ($addToField) {
            $this->data[$field] = (array) $this->data[$field];
            $this->data[$field][] = $value;
        } else {
            $this->data[$field] = $value;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getField($field, $fieldIsCode=false, $valueIsCode=false)
    {
        if ($fieldIsCode) {
            $field = $this->decodeField($field);
        }

        if (!in_array($field, static::FIELDS)) {
            throw new FormException(
                "Can not get [$field] field. This field not exists in " . get_class($this),
                FormException::CODE_WRONG_FIELD
            );
        }

        if (!array_key_exists($field, $this->data)) {
            throw new FormException(
                "Can not get [$field] field. This field exists in " . get_class($this) . 'but not set',
                FormException::CODE_FIELD_VALUE_NOT_SET
            );
        }

        return $valueIsCode
            ? $this->encodeValue($this->data[$field])
            : $this->data[$field];
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
    public static function buildHtmlFormData(array $data): string
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

    public function getSelectors(): array
    {
        return [];
    }

    public function decodeField(string $code): string
    {
        if (array_key_exists($code, $this->fieldCodes)) {
            return $this->fieldCodes[$code];
        }

        throw new FormException(
            "Can not add field with code [$code]. This field code is not defined in " . get_class($this),
            FormException::CODE_WRONG_FIELD_CODE
        );
    }

    public function decodeValue($code)
    {
        if (is_array($code)) {
            $result = [];
            foreach ($code  as $scalarCode) {
                $result[] = $this->decodeScalarValue($scalarCode);
            }

            return $result;
        }

        return $this->decodeScalarValue($code);
    }

    private function decodeScalarValue($code)
    {
        if (array_key_exists($code, $this->valueCodes)) {
            return $this->valueCodes[$code];
        }

        throw new FormException(
            "Can not add value with code [$code]. This value code is not defined in " . get_class($this),
            FormException::CODE_WRONG_VALUE_CODE
        );
    }

    public function encodeValue($value)
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value  as $scalarValue) {
                $result[] = $this->encodeScalarValue($scalarValue);
            }

            return $result;
        }

        return $this->encodeScalarValue($value);
    }

    /**
     * @throws FormException
     */
    public function encodeScalarValue($value)
    {
        $code = array_search($value, $this->valueCodes);
        if ($code !== false) {
            return $code;
        }

        throw new FormException(
            "Can not found a code for value [$value]. The code for this value is not defined in " . get_class($this),
            FormException::CODE_WRONG_VALUE
        );
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

    /**
     * @param array $codes
     * @return $this
     */
    public function setFieldCodes(array $codes): self
    {
        $this->fieldCodes = $codes;

        return $this;
    }

    /**
     * @param array $codes
     * @return $this
     */
    public function setValueCodes(array $codes): self
    {
        $this->valueCodes = $codes;

        return $this;
    }
}