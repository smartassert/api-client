<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\GitSourceClient;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class GetTest extends AbstractSourceClientTestCase
{
    use InvalidJsonResponseExceptionDataProviderTrait;
    use NetworkErrorExceptionDataProviderTrait;

    public function testGetRequestProperties(): void
    {
        $id = md5((string) rand());
        $label = md5((string) rand());
        $hostUrl = md5((string) rand());
        $path = md5((string) rand());

        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'git_source' => [
                    'id' => $id,
                    'label' => $label,
                    'host_url' => $hostUrl,
                    'path' => $path,
                    'has_credentials' => false,
                ],
            ])
        ));

        $apiKey = md5((string) rand());

        $this->client->get($apiKey, $id);

        $request = $this->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ' . $apiKey, $request->getHeaderLine('authorization'));
        self::assertStringEndsWith('/git-source/' . $id, (string) $request->getUri());
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
            $this->client->get('api key', 'id');
        };
    }
}
