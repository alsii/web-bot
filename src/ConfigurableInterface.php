<?php
namespace Alsi\WebBot;

interface ConfigurableInterface
{
    /**
     * @param $config
     * @return ConfigurableInterface
     */
    public function setConfig($config): ConfigurableInterface;
}