<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\ServiceRequest\Deserializer\Error\BadRequestErrorDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Error\Deserializer as ErrorDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Error\DuplicateObjectErrorDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Error\ErrorParameterDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Error\ModifyReadOnlyEntityDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Error\StorageErrorDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Parameter\Deserializer as ParameterDeserializer;
use webignition\HttpHistoryContainer\Container as HttpHistoryContainer;
use webignition\HttpHistoryContainer\MiddlewareFactory;

abstract class AbstractClientTestCase extends TestCase
{
    use CommonNonSuccessResponseDataProviderTrait;

    protected const API_KEY = 'api-key';

    protected MockHandler $mockHandler;
    protected HttpClient $httpClient;

    protected ExceptionFactory $exceptionFactory;
    private HttpHistoryContainer $httpHistoryContainer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();

        $handlerStack = HandlerStack::create($this->mockHandler);

        $this->httpHistoryContainer = new HttpHistoryContainer();
        $handlerStack->push(MiddlewareFactory::create($this->httpHistoryContainer));

        $this->httpClient = new HttpClient(['handler' => $handlerStack]);

        $errorFieldDeserializer = new ErrorParameterDeserializer(new ParameterDeserializer());

        $this->exceptionFactory = new ExceptionFactory(
            new ErrorDeserializer([
                new BadRequestErrorDeserializer($errorFieldDeserializer),
                new DuplicateObjectErrorDeserializer($errorFieldDeserializer),
                new ModifyReadOnlyEntityDeserializer(),
                new StorageErrorDeserializer(),
            ]),
        );
    }

    /**
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    #[DataProvider('clientActionThrowsExceptionDataProvider')]
    public function testClientActionThrowsException(
        ClientExceptionInterface|ResponseInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $exception = null;

        try {
            ($this->createClientActionCallable())();
        } catch (\Throwable $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf($expectedExceptionClass, $exception->getInnerException());
    }

    /**
     * @return array<mixed>
     */
    abstract public static function clientActionThrowsExceptionDataProvider(): array;

    #[DataProvider('commonNonSuccessResponseDataProvider')]
    public function testClientActionThrowsHttpException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        $exception = null;

        try {
            ($this->createClientActionCallable())();
        } catch (\Throwable $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $httpException = $exception->getInnerException();
        self::assertInstanceOf(HttpException::class, $httpException);
        self::assertSame($httpFixture, $httpException->getResponse());
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
