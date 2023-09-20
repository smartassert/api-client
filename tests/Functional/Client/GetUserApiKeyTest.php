<?php

declare(strict_types=1);

namespace Functional\Client;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ApiClient\Model\ApiKey;
use SmartAssert\ApiClient\Tests\Functional\Client\AbstractClientModelCreationTestCase;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class GetUserApiKeyTest extends AbstractClientModelCreationTestCase
{
    use InvalidJsonResponseExceptionDataProviderTrait;
    use NetworkErrorExceptionDataProviderTrait;

    public function testGetUserApiKeyRequestProperties(): void
    {
        $label = null;
        $key = md5((string) rand());

        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'api_key' => [
                    'label' => $label,
                    'key' => $key,
                ],
            ])
        ));

        $token = md5((string) rand());

        $this->client->getUserApiKey($token);

        $request = $this->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ' . $token, $request->getHeaderLine('authorization'));
    }

    public static function clientActionThrowsExceptionDataProvider(): array
    {
        return array_merge(
            self::networkErrorExceptionDataProvider(),
            self::invalidJsonResponseExceptionDataProvider(),
        );
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->getUserApiKey('token');
        };
    }

    protected function getExpectedModelClass(): string
    {
        return ApiKey::class;
    }
}
