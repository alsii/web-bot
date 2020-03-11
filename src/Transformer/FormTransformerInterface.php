<?php
namespace Alsi\WebBot\Transformer;

use Alsi\WebBot\Form\FormInterface;

interface FormTransformerInterface
{
    /**
     * @param array $data
     * @return FormInterface
     */
    public function transform($data): FormInterface;
}