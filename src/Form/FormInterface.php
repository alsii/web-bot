<?php
namespace Alsi\WebBot\Form;

interface FormInterface
{
    public function setField($key, $data): FormInterface;

    public function getData(): array;

    public function getHtmlFormData(): string;

    public function validate();
}