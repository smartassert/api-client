<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\File;

use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;

class CreateReadTest extends AbstractFileTestCase
{
    public function testCreateUnauthorized(): void
    {
        try {
            self::$fileClient->create(
                md5((string) rand()),
                md5((string) rand()),
                md5((string) rand()) . '.yaml',
                md5((string) rand())
            );
            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame(404, $e->getStatusCode());
        }
    }

    public function testReadUnauthorized(): void
    {
        try {
            self::$fileClient->read(
                md5((string) rand()),
                md5((string) rand()),
                md5((string) rand()) . '.yaml'
            );
            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame(404, $e->getStatusCode());
        }
    }

    public function testCreateReadSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());

        $fileSource = self::$fileSourceClient->create($apiKey->key, $label);

        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        self::$fileClient->create($apiKey->key, $fileSource->id, $filename, $content);

        $readContent = self::$fileClient->read($apiKey->key, $fileSource->id, $filename);

        self::assertSame($content, $readContent);
    }
}
