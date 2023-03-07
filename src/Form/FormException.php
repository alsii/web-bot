<?php
namespace Alsi\WebBot\Form;

use RuntimeException;

class FormException extends RuntimeException
{
    public const CODE_WRONG_FIELD = 101;
    public const CODE_WRONG_FIELD_CODE = 102;

    public const CODE_WRONG_VALUE = 103;
    public const CODE_WRONG_VALUE_CODE = 104;

    public const CODE_FIELD_VALUE_NOT_SET = 105;

    public const CODE_WRONG_SUBFORM = 201;
    public const CODE_TOO_MANY_SUBFORMS = 202;
}