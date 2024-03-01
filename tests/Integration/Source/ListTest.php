<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Source;

use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ApiClient\Tests\Services\DataRepository;
use SmartAssert\ApiClient\Tests\Services\SourcesRepository;

class ListTest extends AbstractIntegrationTestCase
{
    public function testListUnauthorized(): void
    {
        $exception = null;

        try {
            self::$sourceClient->list(md5((string) rand()));
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    public function testListSuccess(): void
    {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSources();

        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $expectedSources = [];
        self::assertSame($expectedSources, self::$sourceClient->list($apiKey->key));

        $expectedSources[] = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));
        self::assertEquals($expectedSources, self::$sourceClient->list($apiKey->key));

        $expectedSources[] = self::$gitSourceClient->create(
            $apiKey->key,
            md5((string) rand()),
            md5((string) rand()),
            md5((string) rand()),
            null
        );
        self::assertEquals($expectedSources, self::$sourceClient->list($apiKey->key));

        self::$sourceClient->delete($apiKey->key, $expectedSources[0]->id);
        self::assertEquals([$expectedSources[1]], self::$sourceClient->list($apiKey->key));
    }
}
