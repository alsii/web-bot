<?php
namespace Alsi\WebBot\Step;

use RuntimeException;

class StepException extends RuntimeException
{
    public const CODE_NO_HTTP_CLIENT = 101;
    public const CODE_NO_PRODUCT = 102;
    public const CODE_UNAVAILABLE_PRODUCT = 103;
    public const CODE_NO_ADDRESS = 104;
    public const CODE_NO_CSRF_TOKEN = 105;
    public const CODE_NO_USERNAME = 106;
    public const CODE_NO_PASSWORD = 107;
    public const CODE_NO_ORDER_FORM = 108;
    public const CODE_NO_SHOP_PRODUCT_URL = 109;

    public const CODE_DATA_TRANSFORMATION_ERROR = 150;

    public const CODE_WRONG_HTTP_STATUS = 201;
    public const CODE_WRONG_HTTP_CONTENT = 202;
}