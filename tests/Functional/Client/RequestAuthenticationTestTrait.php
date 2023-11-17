<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

use GuzzleHttp\Handler\MockHandler;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait RequestAuthenticationTestTrait
{
    public function testRequestAuthentication(): void
    {
        $this->getMockHandler()->append($this->getResponseFixture());

        ($this->createClientActionCallable())();

        $request = $this->getLastRequest();
        Assert::assertSame('Bearer ' . $this->getExpectedBearer(), $request->getHeaderLine('authorization'));
    }

    abstract protected function getResponseFixture(): ResponseInterface|\Throwable;

    abstract protected function getExpectedRequestProperties(): ExpectedRequestProperties;

    abstract protected function createClientActionCallable(): callable;

    abstract protected function getLastRequest(): RequestInterface;

    abstract protected function getMockHandler(): MockHandler;

    abstract protected function getExpectedBearer(): string;
}
