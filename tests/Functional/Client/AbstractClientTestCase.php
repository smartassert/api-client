<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use webignition\HttpHistoryContainer\Container as HttpHistoryContainer;

abstract class AbstractClientTestCase extends TestCase
{
    use CommonNonSuccessResponseDataProviderTrait;

    protected const API_KEY = 'api-key';

    protected MockHandler $mockHandler;
    protected HttpClient $httpClient;
    private HttpHistoryContainer $httpHistoryContainer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();

        $handlerStack = HandlerStack::create($this->mockHandler);

        $this->httpHistoryContainer = new HttpHistoryContainer();
        $handlerStack->push(Middleware::history($this->httpHistoryContainer));

        $this->httpClient = new HttpClient(['handler' => $handlerStack]);
    }

    /**
     * @dataProvider clientActionThrowsExceptionDataProvider
     *
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    public function testClientActionThrowsException(
        ClientExceptionInterface|ResponseInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        ($this->createClientActionCallable())();
    }

    /**
     * @return array<mixed>
     */
    abstract public static function clientActionThrowsExceptionDataProvider(): array;

    /**
     * @dataProvider commonNonSuccessResponseDataProvider
     */
    public function testClientActionThrowsHttpException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        try {
            ($this->createClientActionCallable())();

            self::fail(HttpException::class . ' not thrown');
        } catch (HttpException $e) {
            self::assertSame($httpFixture, $e->response);
        }
    }

    protected function getLastRequest(): RequestInterface
    {
        $request = $this->httpHistoryContainer->getTransactions()->getRequests()->getLast();
        \assert($request instanceof RequestInterface);

        return $request;
    }

    abstract protected function createClientActionCallable(): callable;

    protected function getMockHandler(): MockHandler
    {
        return $this->mockHandler;
    }
}
