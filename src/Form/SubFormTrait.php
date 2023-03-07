<?php
/** @noinspection PhpUndefinedClassConstantInspection */

namespace Alsi\WebBot\Form;

/**
 * Trait SubormTrait
 * @package Alsi\WebBot\Form
 */
trait SubFormTrait
{
    protected $data = [];

    /**
     * @param FormInterface $form
     * @return FormInterface
     */
    public function addSubForm(FormInterface $form): FormInterface
    {
        $key = get_class($form);

        if (!array_key_exists($key, static::SUBFORMS)) {
            throw new FormException(
                $key . ' can not be added as subform',
                FormException::CODE_WRONG_SUBFORM,
            );
        }
        $sfDef = static::SUBFORMS[$key];

        if (!array_key_exists($sfDef[SubformInterface::SF_TOTAL], $this->data)) {
            $this->data[$sfDef[SubformInterface::SF_TOTAL]] = 0;
        }

        $maxForms = $this->data[$sfDef[SubformInterface::SF_MAX]] ?? 0;
        if ($this->data[$sfDef[SubformInterface::SF_TOTAL]] >= ($maxForms)) {
            throw new FormException(
                "Too many $key subforms. Max. $maxForms possible",
                FormException::CODE_TOO_MANY_SUBFORMS
            );
        }

        $count = $this->data[$sfDef[SubformInterface::SF_TOTAL]]++;
        foreach ($form->getData() as $k => $v) {
            $this->data["$count-$k"] = $v;
        }

        return $this;
    }
}