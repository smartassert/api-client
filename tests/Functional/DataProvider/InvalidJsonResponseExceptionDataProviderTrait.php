<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\DataProvider;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ApiClient\Exception\Http\UnexpectedContentTypeException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedDataException;

trait InvalidJsonResponseExceptionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function invalidJsonResponseExceptionDataProvider(): array
    {
        return [
            'invalid response content type' => [
                'httpFixture' => new Response(200, ['content-type' => 'text/plain']),
                'expectedExceptionClass' => UnexpectedContentTypeException::class,
            ],
            'invalid response data' => [
                'httpFixture' => new Response(200, ['content-type' => 'application/json'], '1'),
                'expectedExceptionClass' => UnexpectedDataException::class,
            ],
        ];
    }
}
