<?php
namespace Alsi\WebBot;

use Alsi\WebBot\Step\StepException;
use Alsi\WebBot\Step\StepInterface;

class WebBot
{
    /**
     * @var StepInterface[]
     */
    private $steps;

    /**
     * @param StepInterface $step
     * @return WebBot
     */
    public function addStep(StepInterface $step): WebBot
    {
        $this->steps[] = $step;

        return $this;
    }

    /**
     * @param array $state
     * @return bool
     */
    public function register($state = []): bool
    {
        foreach($this->steps as $step) {
            echo PHP_EOL, '==== Trying ', get_class($step), PHP_EOL;
            try {
                $state = $step->execute($state);
            } catch (StepException $e) {
                echo PHP_EOL, 'Step Exception on ', get_class($step), '. Message: ', $e->getMessage(), PHP_EOL;
                return false;
            }
            echo PHP_EOL, '---- Done ', get_class($step), PHP_EOL;
        }

        return true;
    }
}