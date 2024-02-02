<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\UsersClient;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Exception\IncompleteResponseDataException;
use SmartAssert\ApiClient\Tests\Functional\Client\ExpectedRequestProperties;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestHasNoAuthenticationTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestPropertiesTestTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class RefreshTokenTest extends AbstractUsersClientTestCase
{
    use InvalidJsonResponseExceptionDataProviderTrait;
    use NetworkErrorExceptionDataProviderTrait;
    use RequestPropertiesTestTrait;
    use RequestHasNoAuthenticationTestTrait;

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
            'token missing' => [
                'payload' => ['refresh_token' => md5((string) rand())],
                'expectedMissingKey' => 'token',
            ],
            'refresh_token missing' => [
                'payload' => ['token' => md5((string) rand())],
                'expectedMissingKey' => 'refresh_token',
            ],
        ];
    }

    public function testRefreshTokenThrowsIncompleteResponseDataException(): void
    {
        $responseData = ['token' => 'token'];
        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode($responseData)
        ));

        $exception = null;

        try {
            ($this->createClientActionCallable())();
        } catch (IncompleteResponseDataException $exception) {
        }

        self::assertInstanceOf(IncompleteResponseDataException::class, $exception);
        self::assertSame('post_user_token_refresh', $exception->requestName);
        self::assertSame('refresh_token', $exception->incompleteDataException->missingKey);
        self::assertSame($responseData, $exception->incompleteDataException->data);
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->refreshToken('refresh-token');
        };
    }

    protected function getResponseFixture(): ResponseInterface
    {
        return new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'token' => 'token',
                'refresh_token' => 'refresh_token',
            ])
        );
    }

    protected function getExpectedRequestProperties(): ExpectedRequestProperties
    {
        return new ExpectedRequestProperties('POST', '/user/frontend-token/refresh');
    }
}
