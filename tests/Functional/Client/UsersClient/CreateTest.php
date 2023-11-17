<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\UsersClient;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Model\User;
use SmartAssert\ApiClient\Tests\Functional\Client\ClientActionThrowsInvalidModelDataExceptionTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\ExpectedRequestProperties;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestAuthenticationTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestPropertiesTestTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class CreateTest extends AbstractUsersClientTestCase
{
    use ClientActionThrowsInvalidModelDataExceptionTestTrait;
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

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->create('admin token', self::IDENTIFIER, 'password');
        };
    }

    protected function getExpectedBearer(): string
    {
        return 'admin token';
    }

    protected function getExpectedModelClass(): string
    {
        return User::class;
    }

    protected function getResponseFixture(): ResponseInterface
    {
        return new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'user' => [
                    'id' => self::ID,
                    'user-identifier' => self::IDENTIFIER,
                ],
            ])
        );
    }

    protected function getExpectedRequestProperties(): ExpectedRequestProperties
    {
        return new ExpectedRequestProperties('POST', '/user/create');
    }
}
