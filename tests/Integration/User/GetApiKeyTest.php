<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Data\User\ApiKey;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Exception\User\ApiKeyNotFoundException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ApiClient\Tests\Services\DataRepository;

class GetApiKeyTest extends AbstractIntegrationTestCase
{
    public function testGetUserApiKeyInvalidToken(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$usersClient->getApiKey(md5((string) rand()));
    }

    public function testUserApiKeyNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user = self::$usersClient->verifyToken($refreshableToken->token);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $usersDataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=users;user=postgres;password=password!'
        );
        $usersDataRepository->query('DELETE FROM api_key WHERE owner_id = \'' . $user->id . '\'');

        $exception = null;

        try {
            self::$usersClient->getApiKey($refreshableToken->token);
        } catch (ApiKeyNotFoundException $exception) {
        }

        self::assertEquals(new ApiKeyNotFoundException(), $exception);

        $usersDataRepository->query(
            'INSERT INTO api_key (id, owner_id) VALUES (\'' . $apiKey->key . '\', \'' . $user->id . '\')'
        );
    }

    public function testGetUserApiKeySuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        self::assertInstanceOf(ApiKey::class, $apiKey);
    }
}
