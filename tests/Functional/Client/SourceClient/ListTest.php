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

class ListTest extends AbstractSourceClientTestCase
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

    public function testListThrowsIncompleteResponseDataException(): void
    {
        $responseData = [
            [
                'type' => 'file',
                'id' => 'file source id',
            ],
        ];

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
        self::assertSame('get_sources', $exception->requestName);
        self::assertSame('label', $exception->incompleteDataException->missingKey);
        self::assertSame($responseData[0], $exception->incompleteDataException->data);
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->list(self::API_KEY);
        };
    }

    protected function getResponseFixture(): ResponseInterface
    {
        return new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                [
                    'type' => 'file',
                    'id' => 'file source id',
                    'label' => 'file source label',
                ],
            ])
        );
    }

    protected function getExpectedRequestProperties(): ExpectedRequestProperties
    {
        return new ExpectedRequestProperties('GET', '/sources');
    }
}
