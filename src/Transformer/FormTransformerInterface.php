<?php
namespace Alsi\WebBot\Transformer;

use Alsi\WebBot\Form\FormInterface;

interface FormTransformerInterface
{
    public function transform(array $data): FormInterface;

    public function getSelectors(): array;

    public function setForm(FormInterface $form): FormTransformerInterface;

    public function getForm(): FormInterface;
}