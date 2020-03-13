<?php
namespace Alsi\WebBot\Step;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;

trait StepTrait
{
    /**
     * @param ResponseInterface $response
     */
    protected function checkStatusCode(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            throw new StepException(
                "Wrong HTTP Status code ($statusCode)",
                StepException::CODE_WRONG_HTTP_STATUS,
                null,
                ['response' => $response],
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