<?php


namespace Alsi\WebBot;


use Psr\Log\LoggerAwareInterface as BaseLoggerAwareInterface;

interface LoggerAwareInterface extends BaseLoggerAwareInterface
{

    public const LOG_ENTITY_ID = 'entity-id';
}