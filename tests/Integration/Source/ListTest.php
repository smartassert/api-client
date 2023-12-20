<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Source;

use SmartAssert\ApiClient\FooException\Http\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Services\DataRepository;

class ListTest extends AbstractSourceTestCase
{
    public function testListUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$sourceClient->list(md5((string) rand()));
    }

    public function testListSuccess(): void
    {
        $sourcesDataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=sources;user=postgres;password=password!'
        );
        $sourcesDataRepository->removeAllFor(['file_source', 'git_source', 'source', 'suite']);

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
