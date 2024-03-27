<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\JobCoordinator;

use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use Symfony\Component\Uid\Ulid;

class ListTest extends AbstractJobCoordinatorClientTestCase
{
    public function testListUnauthorized(): void
    {
        $exception = null;

        $suiteId = (string) new Ulid();
        \assert('' !== $suiteId);

        try {
            $this->jobCoordinatorClient->list(md5((string) rand()), $suiteId);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    public function testListSuccess(): void
    {
        $user1RefreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user1ApiKey = self::$usersClient->getApiKey($user1RefreshableToken->token);

        $user2RefreshableToken = self::$usersClient->createToken(self::USER2_EMAIL, self::USER2_PASSWORD);
        $user2ApiKey = self::$usersClient->getApiKey($user2RefreshableToken->token);

        $suite1Id = (string) new Ulid();
        \assert('' !== $suite1Id);

        $suite2Id = (string) new Ulid();
        \assert('' !== $suite2Id);

        $jobSummaries = [];

        $job = $this->jobCoordinatorClient->create($user1ApiKey->key, $suite1Id, rand(1, 10000));
        $jobSummaries[] = $job->summary;

        $job = $this->jobCoordinatorClient->create($user1ApiKey->key, $suite2Id, rand(1, 10000));
        $jobSummaries[] = $job->summary;

        $job = $this->jobCoordinatorClient->create($user1ApiKey->key, $suite1Id, rand(1, 10000));
        $jobSummaries[] = $job->summary;

        $job = $this->jobCoordinatorClient->create($user2ApiKey->key, $suite1Id, rand(1, 10000));
        $jobSummaries[] = $job->summary;

        $retrievedSummaries = $this->jobCoordinatorClient->list($user1ApiKey->key, $suite1Id);

        $expected = [
            $jobSummaries[2],
            $jobSummaries[0],
        ];

        self::assertEquals($expected, $retrievedSummaries);
    }
}
