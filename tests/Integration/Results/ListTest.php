<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Results;

use SmartAssert\ApiClient\Exception\ClientExceptionInterface;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use Symfony\Component\Uid\Ulid;

class ListTest extends AbstractJobCoordinatorClientTestCase
{
    public function testListUnauthorized(): void
    {
        $exception = null;
        $jobLabel = (string) new Ulid();

        try {
            $this->resultsEventClient->list(md5((string) rand()), $jobLabel, null, null);
        } catch (ClientExceptionInterface $exception) {
        }

        self::assertInstanceOf(UnauthorizedException::class, $exception);
    }

    public function testListSuccess(): void
    {
        $user1RefreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user1ApiKey = self::$usersClient->getApiKey($user1RefreshableToken->token);

        $emptyList = $this->resultsEventClient->list($user1ApiKey->key, (string) new Ulid(), null, null);
        self::assertSame([], $emptyList);
    }
}
