<?php
namespace Alsi\WebBotTests\Step;

use Alsi\WebBot\LoggerAwareInterface;
use Alsi\WebBot\Step\StepException;
use Alsi\WebBot\Step\StepInterface;
use Alsi\WebBot\Step\StepTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DomCrawler\Crawler;

class StepTraitTest extends TestCase implements LoggerAwareInterface, StepInterface
{
    use StepTrait {
        __construct as traitConstructor;
    }

    private const CONTENTS = 'Contents';
    private const CONTEXT = ['id' => 20];

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @param array $state
     * @return array
     */
    public function execute($state): array
    {
        return $state;
    }

    public function testTraitConstructor()
    {
        $this->traitConstructor();
        $this->assertInstanceOf(NullLogger::class, $this->logger);
    }

    public function testCheckStatusCodeSuccess(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->never())
            ->method('critical')
        ;

        $this->setLogger($logger);

        $this->checkStatusCode($this->createResponse(self::CONTENTS, 200), self::CONTEXT);
    }

    public function testCheckStatusCodeFailure(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with(self::CONTENTS, self::CONTEXT)
        ;

        $this->setLogger($logger);

        $this->expectException(StepException::class);
        $this->expectExceptionCode(StepException::CODE_WRONG_HTTP_STATUS);

        $this->checkStatusCode($this->createResponse(self::CONTENTS, 400), self::CONTEXT);
    }

    public function testFind()
    {
        $crawler = $this->createCrawler(1);
        $this->find($crawler, '');

        $this->assertTrue(true);

        $this->expectException(StepException::class);
        $this->expectExceptionCode(StepException::CODE_WRONG_HTTP_CONTENT);

        $crawler = $this->createCrawler(0);
        $this->find($crawler, '');
    }

    public function testFindInputByName()
    {
        $inputName = 'testInputName';

        /** @var MockObject $mockCrawler */
        $mockCrawler = $this->createCrawler(1);
        $mockCrawler
            ->expects($this->once())
            ->method('filter')
            ->with("input[name={$inputName}]")
        ;

        /** @var Crawler $crawler */
        $crawler = $mockCrawler;
        $this->findInputByName($crawler, $inputName);
    }

    private function createResponse(string $contents, int $status): ResponseInterface
    {
        $stream = $this->createStub(StreamInterface::class);
        $stream->method('getContents')->willReturn($contents);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($status);
        $response->method('getBody')->willReturn($stream);

        return $response;
    }

    private function createCrawler(int $count): Crawler {
        $element = $this->createStub(Crawler::class);
        $element->method('count')->willReturn($count);

        $crawler = $this->createMock(Crawler::class);
        $crawler->method('filter')->willReturn($element);

        return $crawler;
    }
}