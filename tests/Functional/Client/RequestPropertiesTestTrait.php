<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

use GuzzleHttp\Handler\MockHandler;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait RequestPropertiesTestTrait
{
    public function testRequestProperties(): void
    {
        $this->getMockHandler()->append($this->getResponseFixture());

        try {
            ($this->createClientActionCallable())();
        } catch (\Exception $exception) {
            var_dump($exception);
        }

        $request = $this->getLastRequest();
        Assert::assertSame($this->getExpectedRequestProperties()->method, $request->getMethod());
        Assert::assertStringEndsWith($this->getExpectedRequestProperties()->url, (string) $request->getUri());
    }

    abstract protected function getResponseFixture(): ResponseInterface|\Throwable;

    abstract protected function getExpectedRequestProperties(): ExpectedRequestProperties;

    abstract protected function createClientActionCallable(): callable;

    abstract protected function getLastRequest(): RequestInterface;

    abstract protected function getMockHandler(): MockHandler;
}
