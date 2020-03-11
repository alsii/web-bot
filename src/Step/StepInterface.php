<?php
namespace Alsi\WebBot\Step;

interface StepInterface
{
    /**
     * @param array $state
     * @return array
     */
    public function execute($state): array;
}