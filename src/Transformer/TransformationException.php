<?php
namespace Alsi\WebBot\Transformer;

use RuntimeException;

class TransformationException extends RuntimeException
{
    public const CODE_PATH_NOT_EXISTS = 100;
    public const CODE_WRONG_OPTION = 102;
    public const CODE_UNKNOWN_MAP_KEY = 103;

    public const CODE_UNKNOWN_MIME = 201;
    public const CODE_CAN_NOT_GET_FILE_CONTENT = 201;
}