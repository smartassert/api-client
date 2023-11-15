<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\FileSourceClient;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ApiClient\Model\Source\FileSource;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;

class DeleteTest extends AbstractFileSourceClientTestCase
{
    use InvalidJsonResponseExceptionDataProviderTrait;
    use NetworkErrorExceptionDataProviderTrait;

    public function testDeleteThrowsInvalidModelDataException(): void
    {
        $responsePayload = ['key' => 'value'];
        $response = new Response(200, ['content-type' => 'application/json'], (string) json_encode($responsePayload));

        $this->mockHandler->append($response);

        try {
            $this->client->delete('api key', 'id');
            self::fail(InvalidModelDataException::class . ' not thrown');
        } catch (InvalidModelDataException $e) {
            self::assertSame(FileSource::class, $e->class);
            self::assertSame($response, $e->response);
            self::assertSame($responsePayload, $e->payload);
        }
    }

    public function testDeleteRequestProperties(): void
    {
        $id = md5((string) rand());
        $label = md5((string) rand());
        $deletedAt = 123;

        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'file_source' => [
                    'id' => $id,
                    'label' => $label,
                    'deleted_at' => $deletedAt,
                ],
            ])
        ));

        $apiKey = md5((string) rand());

        $this->client->delete($apiKey, $id);

        $request = $this->getLastRequest();
        self::assertSame('DELETE', $request->getMethod());
        self::assertSame('Bearer ' . $apiKey, $request->getHeaderLine('authorization'));
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
            $this->client->delete('api key', 'id');
        };
    }
}
