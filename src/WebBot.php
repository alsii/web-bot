<?php
namespace Alsi\WebBot;

use Alsi\WebBot\Step\StepException;
use Alsi\WebBot\Step\StepInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class WebBot implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const RESULT_SUCCESSFUL = 'success';

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
     * @return array
     */
    public function register($state = []): array
    {
        $logId = $state[self::LOG_ENTITY_ID] ?? null;
        $logContext = $logId ? [self::LOG_ENTITY_ID => $logId] : [];

        foreach($this->steps as $step) {
            $this->logger->info('==== Trying ' . get_class($step), $logContext);
            try {
                $state = $step->execute($state);
            } catch (StepException $e) {
                /** @var  $response ResponseInterface */
                $responseContent = $response ? $response->getBody()->getContents() : '';

                $this->logger->critical(
                    'Step Exception(' . $e->getCode() . ') on ' . get_class($step) .
                    ': ' . $e->getMessage() . PHP_EOL . $responseContent,
                    $logContext
                );

                $state['web-bot'] = [self::RESULT_SUCCESSFUL => false];

                return $state;
            }
            $this->logger->info('---- Done ' . get_class($step), $logContext);
        }

        $state['web-bot'] = [self::RESULT_SUCCESSFUL => true];

        return $state;
    }
}