<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\UsersClient;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Exception\IncompleteResponseDataException;
use SmartAssert\ApiClient\Tests\Functional\Client\ExpectedRequestProperties;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestAuthenticationTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestPropertiesTestTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class GetApiKeyTest extends AbstractUsersClientTestCase
{
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
            'key missing' => [
                'payload' => [],
                'expectedMissingKey' => 'key',
            ],
        ];
    }

    public function testGetApiKeyThrowsIncompleteResponseDataException(): void
    {
        $responseData = ['label' => 'label'];
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
        self::assertSame('get_user_apikey', $exception->requestName);
        self::assertSame('key', $exception->incompleteDataException->missingKey);
        self::assertSame($responseData, $exception->incompleteDataException->data);
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->getApiKey(self::API_KEY);
        };
    }

    protected function getResponseFixture(): ResponseInterface
    {
        return new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'label' => 'label',
                'key' => 'key',
            ])
        );
    }

    protected function getExpectedRequestProperties(): ExpectedRequestProperties
    {
        return new ExpectedRequestProperties('GET', '/user/apikey');
    }

    protected function getExpectedAuthorizationHeader(): string
    {
        return 'Bearer ' . self::API_KEY;
    }
}
