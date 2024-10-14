<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;

trait ClientActionThrowsIncompleteDataExceptionTestTrait
{
    /**
     * @param array<mixed>     $payload
     * @param non-empty-string $expectedMissingKey
     */
    #[DataProvider('incompleteResponseDataExceptionDataProvider')]
    public function testClientActionThrowsIncompleteDataException(
        array $payload,
        string $expectedRequestName,
        string $expectedMissingKey,
    ): void {
        $response = new Response(200, ['content-type' => 'application/json'], (string) json_encode($payload));

        $this->mockHandler->append($response);

        $exception = null;

        try {
            ($this->createClientActionCallable())();
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertSame($expectedRequestName, $exception->getRequestName());

        $innerException = $exception->getInnerException();
        self::assertInstanceOf(IncompleteDataException::class, $innerException);

        self::assertSame($expectedMissingKey, $innerException->missingKey);
        self::assertSame($payload, $innerException->data);
    }

    /**
     * @return array<array{
     *   payload: array<mixed>,
     *   expectedRequestName: non-empty-string,
     *   expectedMissingKey: non-empty-string
     *  }>
     */
    abstract public static function incompleteResponseDataExceptionDataProvider(): array;

    abstract protected function createClientActionCallable(): callable;
}
