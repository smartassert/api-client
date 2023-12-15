<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\UsersClient;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Tests\Functional\Client\ExpectedRequestProperties;
use SmartAssert\ApiClient\Tests\Functional\Client\FooClientActionThrowsIncompleteDataExceptionTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestAuthenticationTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestPropertiesTestTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\FooInvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\FooNetworkErrorExceptionDataProviderTrait;

class CreateTest extends AbstractUsersClientTestCase
{
    use FooClientActionThrowsIncompleteDataExceptionTestTrait;
    use FooInvalidJsonResponseExceptionDataProviderTrait;
    use FooNetworkErrorExceptionDataProviderTrait;
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

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->create('admin token', self::IDENTIFIER, 'password');
        };
    }

    protected function getExpectedAuthorizationHeader(): string
    {
        return 'admin token';
    }

    protected function getResponseFixture(): ResponseInterface
    {
        return new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode(['id' => self::ID, 'user-identifier' => self::IDENTIFIER])
        );
    }

    protected function getExpectedRequestProperties(): ExpectedRequestProperties
    {
        return new ExpectedRequestProperties('POST', '/user/create');
    }
}
