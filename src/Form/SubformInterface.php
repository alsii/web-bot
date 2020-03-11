<?php
namespace Alsi\WebBot\Form;

interface SubformInterface
{
    public const SF_TOTAL = 'total';
    public const SF_MAX = 'max';

    public function addSubForm(FormInterface $form): FormInterface;
}