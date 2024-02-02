<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ApiClient\Exception\IncompleteResponseDataException;

trait ClientActionThrowsIncompleteResponseDataExceptionTestTrait
{
    /**
     * @dataProvider incompleteResponseDataExceptionDataProvider
     *
     * @param array<mixed>     $payload
     * @param non-empty-string $expectedMissingKey
     */
    public function testClientActionThrowsIncompleteResponseDataException(
        array $payload,
        string $expectedRequestName,
        string $expectedMissingKey,
    ): void {
        $response = new Response(200, ['content-type' => 'application/json'], (string) json_encode($payload));

        $this->mockHandler->append($response);

        $exception = null;

        try {
            ($this->createClientActionCallable())();
        } catch (IncompleteResponseDataException $exception) {
        }

        self::assertInstanceOf(IncompleteResponseDataException::class, $exception);
        self::assertSame($expectedRequestName, $exception->requestName);
        self::assertSame($expectedMissingKey, $exception->incompleteDataException->missingKey);
        self::assertSame($payload, $exception->incompleteDataException->data);
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
