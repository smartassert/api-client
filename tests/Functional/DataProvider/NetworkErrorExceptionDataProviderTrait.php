<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\DataProvider;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use SmartAssert\ApiClient\Exception\Http\FailedRequestException;

trait NetworkErrorExceptionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function networkErrorExceptionDataProvider(): array
    {
        return [
            'network error' => [
                'httpFixture' => new ConnectException('Exception message', new Request('GET', '/')),
                'expectedExceptionClass' => FailedRequestException::class,
            ],
        ];
    }
}
