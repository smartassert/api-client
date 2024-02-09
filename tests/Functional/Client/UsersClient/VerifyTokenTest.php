<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\UsersClient;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Tests\Functional\Client\ClientActionThrowsIncompleteDataExceptionTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\ExpectedRequestProperties;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestAuthenticationTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestPropertiesTestTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class VerifyTokenTest extends AbstractUsersClientTestCase
{
    use ClientActionThrowsIncompleteDataExceptionTestTrait;
    use InvalidJsonResponseExceptionDataProviderTrait;
    use NetworkErrorExceptionDataProviderTrait;
    use RequestPropertiesTestTrait;
    use RequestAuthenticationTestTrait;

    public static function clientActionThrowsExceptionDataProvider(): array
    {
        return array_merge(
            self::networkErrorExceptionDataProvider(),
            self::invalidJsonResponseExceptionDataProvider(),
        );
    }

    /**
     * @return array<mixed>
     */
    public static function incompleteDataExceptionDataProvider(): array
    {
        return [
            'id missing' => [
                'payload' => ['user-identifier' => md5((string) rand())],
                'expectedMissingKey' => 'id',
            ],
            'user-identifier missing' => [
                'payload' => ['id' => md5((string) rand())],
                'expectedMissingKey' => 'user-identifier',
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function incompleteResponseDataExceptionDataProvider(): array
    {
        return [
            'user-identifier missing' => [
                'payload' => ['id' => self::ID],
                'expectedRequestName' => 'get_user_token_verify',
                'expectedMissingKey' => 'user-identifier',
            ],
        ];
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->verifyToken('frontend token');
        };
    }

    protected function getExpectedAuthorizationHeader(): string
    {
        return 'Bearer frontend token';
    }

    protected function getResponseFixture(): ResponseInterface
    {
        return new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'id' => 'id',
                'user-identifier' => 'identifier',
            ])
        );
    }

    protected function getExpectedRequestProperties(): ExpectedRequestProperties
    {
        return new ExpectedRequestProperties('GET', '/user/frontend-token/verify');
    }
}
