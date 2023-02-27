<?php
namespace Alsi\WebBot\Form;

interface FormInterface
{
    public function setField(string $field, $value, bool $useCode=false): FormInterface;

    public function getData(): array;

    public function getField(string $field, bool $useCode=false);

    public function getHtmlFormData(): string;

    public function validate();
}