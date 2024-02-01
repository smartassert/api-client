<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\SourceClient;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Exception\IncompleteResponseDataException;
use SmartAssert\ApiClient\Tests\Functional\Client\ExpectedRequestProperties;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestAuthenticationTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestPropertiesTestTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class DeleteTest extends AbstractSourceClientTestCase
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

    public function testDeleteThrowsIncompleteResponseDataException(): void
    {
        $responseData = ['type' => 'file', 'id' => self::ID];
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
        self::assertSame('delete_source', $exception->requestName);
        self::assertSame('label', $exception->incompleteDataException->missingKey);
        self::assertSame($responseData, $exception->incompleteDataException->data);
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->delete(self::API_KEY, self::ID);
        };
    }

    protected function getResponseFixture(): ResponseInterface
    {
        return new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'type' => 'file',
                'id' => self::ID,
                'label' => 'source label',
            ])
        );
    }

    protected function getExpectedRequestProperties(): ExpectedRequestProperties
    {
        return new ExpectedRequestProperties('DELETE', '/source/' . self::ID);
    }
}
