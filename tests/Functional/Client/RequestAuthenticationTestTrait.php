<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\RequestInterface;

trait RequestAuthenticationTestTrait
{
    public function testRequestAuthentication(): void
    {
        $this->getMockHandler()->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode($this->getResponsePayload())
        ));

        ($this->createClientActionCallable())();

        $request = $this->getLastRequest();
        Assert::assertSame('Bearer ' . $this->getExpectedBearer(), $request->getHeaderLine('authorization'));
    }

    /**
     * @return array<mixed>
     */
    abstract protected function getResponsePayload(): array;

    abstract protected function getExpectedRequestProperties(): ExpectedRequestProperties;

    abstract protected function createClientActionCallable(): callable;

    abstract protected function getLastRequest(): RequestInterface;

    abstract protected function getMockHandler(): MockHandler;

    abstract protected function getExpectedBearer(): string;
}
