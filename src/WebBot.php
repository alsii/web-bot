<?php
namespace Alsi\WebBot;

use Alsi\WebBot\Step\StepException;
use Alsi\WebBot\Step\StepInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class WebBot
{
    use LoggerAwareTrait;

    public const LOG_ENTITY_ID = 'entity-id';

    /**
     * @var StepInterface[]
     */
    private $steps;

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

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
        $logId = $state[self::LOG_ENTITY_ID] ?? null;
        $logContext = $logId ? [self::LOG_ENTITY_ID => $logId] : [];

        foreach($this->steps as $step) {
            $this->logger->info('==== Trying ' . get_class($step), $logContext);
            try {
                $state = $step->execute($state);
            } catch (StepException $e) {
                /** @var  $response ResponseInterface */
                $response = $e->getContext()['response'] ?? null;
                $responseContent = $response ? $response->getBody()->getContents() : '';

                $this->logger->critical(
                    'Step Exception(' . $e->getCode() . ') on ' . get_class($step) .
                    ': ' . $e->getMessage() . PHP_EOL . $responseContent,
                    $logContext
                );

                return false;
            }
            $this->logger->info('---- Done ' . get_class($step), $logContext);
        }

        return true;
    }
}