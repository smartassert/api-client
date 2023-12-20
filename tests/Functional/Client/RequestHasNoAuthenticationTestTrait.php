<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

use GuzzleHttp\Handler\MockHandler;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait RequestHasNoAuthenticationTestTrait
{
    public function testRequestHasNoAuthentication(): void
    {
        $this->getMockHandler()->append($this->getResponseFixture());

        ($this->createClientActionCallable())();

        $request = $this->getLastRequest();
        Assert::assertSame('', $request->getHeaderLine('authorization'));
    }

    abstract protected function getResponseFixture(): ResponseInterface|\Throwable;

    abstract protected function createClientActionCallable(): callable;

    abstract protected function getLastRequest(): RequestInterface;

    abstract protected function getMockHandler(): MockHandler;
}
