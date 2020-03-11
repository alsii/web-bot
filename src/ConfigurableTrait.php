<?php
namespace Alsi\WebBot;

trait ConfigurableTrait
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param $config
     * @return ConfigurableInterface
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    public function setConfig($config): ConfigurableInterface
    {
        $this->config = $config;

        return $this;
    }
}