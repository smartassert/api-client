<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\FileSource;

use SmartAssert\ApiClient\Exception\Error\BadRequestException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceRequest\Error\BadRequestError;

class CreateTest extends AbstractIntegrationTestCase
{
    use CreateUpdateFileSourceDataProviderTrait;

    public function testCreateUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$fileSourceClient->create(md5((string) rand()), md5((string) rand()));
    }

    /**
     * @dataProvider createUpdateFileSourceBadRequestDataProvider
     */
    public function testCreateBadRequest(string $label, BadRequestError $expected): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $exception = null;

        try {
            self::$fileSourceClient->create($apiKey->key, $label);
        } catch (BadRequestException $exception) {
        }

        self::assertInstanceOf(BadRequestException::class, $exception);
        self::assertEquals($expected, $exception->error);
    }

    public function testCreateFileSourceSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());

        $fileSource = self::$fileSourceClient->create($apiKey->key, $label);

        self::assertSame($label, $fileSource->label);
    }
}
