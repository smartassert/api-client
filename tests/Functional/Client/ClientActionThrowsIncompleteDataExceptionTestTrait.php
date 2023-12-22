<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use SmartAssert\ApiClient\Exception\IncompleteDataException;

trait ClientActionThrowsIncompleteDataExceptionTestTrait
{
    /**
     * @dataProvider incompleteDataExceptionDataProvider
     *
     * @param array<mixed>     $payload
     * @param non-empty-string $expectedMissingKey
     */
    public function testClientActionThrowsIncompleteDataException(array $payload, string $expectedMissingKey): void
    {
        $response = new Response(200, ['content-type' => 'application/json'], (string) json_encode($payload));

        $this->mockHandler->append($response);

        try {
            ($this->createClientActionCallable())();
            Assert::fail(IncompleteDataException::class . ' not thrown');
        } catch (IncompleteDataException $e) {
            Assert::assertSame($payload, $e->data);
            Assert::assertSame($expectedMissingKey, $e->missingKey);
        }
    }

    /**
     * @return array<array{responsePayload: array<mixed>, expectedMissingKey: non-empty-string}>
     */
    abstract public static function incompleteDataExceptionDataProvider(): array;

    abstract protected function createClientActionCallable(): callable;
}
