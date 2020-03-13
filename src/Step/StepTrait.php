<?php
namespace Alsi\WebBot\Step;

use Alsi\WebBot\LoggerAwareInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

trait StepTrait
{
    use LoggerAwareTrait;

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    /**
     * @param ResponseInterface $response
     * @param string $logEntityId
     */
    protected function checkStatusCode(ResponseInterface $response, string $logEntityId): void
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            $this->logger->critical(
                $response->getBody()->getContents(), [LoggerAwareInterface::LOG_ENTITY_ID => $logEntityId]
            );

            throw new StepException(
                "Wrong HTTP Status code ($statusCode)",
                StepException::CODE_WRONG_HTTP_STATUS,
            );
        }
    }

    /**
     * @param Crawler $crawler
     * @param string $name
     * @return Crawler
     */
    protected function findInputByName(Crawler $crawler, $name): Crawler
    {
        return $this->find($crawler, "input[name=$name]");
    }

    /**
     * @param Crawler $crawler
     * @param string $selector
     * @return Crawler
     */
    protected function find(Crawler $crawler, $selector): Crawler
    {
        $element = $crawler->filter($selector);
        if ($element->count() === 0) {
            throw new StepException(
                "Can not send Options: no \"$selector\" found",
                StepException::CODE_WRONG_HTTP_CONTENT
            );
        }

        return $element;
    }
}