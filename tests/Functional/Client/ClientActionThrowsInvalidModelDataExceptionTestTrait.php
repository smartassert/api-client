<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;

trait ClientActionThrowsInvalidModelDataExceptionTestTrait
{
    public function testClientActionThrowsInvalidModelDataException(): void
    {
        $responsePayload = [md5((string) rand()) => md5((string) rand())];
        $response = new Response(200, ['content-type' => 'application/json'], (string) json_encode($responsePayload));

        $this->mockHandler->append($response);

        try {
            ($this->createClientActionCallable())();
            Assert::fail(InvalidModelDataException::class . ' not thrown');
        } catch (InvalidModelDataException $e) {
            Assert::assertSame($this->getExpectedModelClass(), $e->class);
            Assert::assertSame($response, $e->getHttpResponse());
            Assert::assertSame($responsePayload, $e->payload);
        }
    }

    /**
     * @return class-string
     */
    abstract protected function getExpectedModelClass(): string;

    abstract protected function createClientActionCallable(): callable;
}
