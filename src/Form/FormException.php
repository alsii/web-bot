<?php
namespace Alsi\WebBot\Form;

use RuntimeException;

class FormException extends RuntimeException
{
    public const CODE_WRONG_FIELD = 101;
    public const CODE_WRONG_SUBFORM = 201;
    public const CODE_TOO_MANY_SUBFORMS = 202;
}