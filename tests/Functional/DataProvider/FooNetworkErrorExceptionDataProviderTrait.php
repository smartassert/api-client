<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\DataProvider;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use SmartAssert\ApiClient\FooException\Http\HttpClientException;

trait FooNetworkErrorExceptionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function networkErrorExceptionDataProvider(): array
    {
        return [
            'network error' => [
                'httpFixture' => new ConnectException('Exception message', new Request('GET', '/')),
                'expectedExceptionClass' => HttpClientException::class,
            ],
        ];
    }
}
