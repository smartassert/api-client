<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\JobCoordinator;

use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\MetaState;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJobComponent;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use Symfony\Component\Uid\Ulid;

class GetTest extends AbstractJobCoordinatorClientTestCase
{
    public function testGetUnauthorized(): void
    {
        $exception = null;
        $jobId = (string) new Ulid();

        try {
            $this->jobCoordinatorClient->get(md5((string) rand()), $jobId);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    #[DataProvider('getSuccessDataProvider')]
    public function testGetSuccess(int $maximumDurationInSeconds): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $suiteId = (string) new Ulid();

        $createdJob = $this->jobCoordinatorClient->create($apiKey->key, $suiteId, $maximumDurationInSeconds);
        sleep(1);

        $job = $this->jobCoordinatorClient->get($apiKey->key, $createdJob->summary->id);

        self::assertEquals(new MetaState(true, false), $job->metaState);
        self::assertSame($suiteId, $job->summary->suiteId);
        self::assertSame($maximumDurationInSeconds, $job->summary->maximumDurationInSeconds);

        $expectedRequestStates = ['pending', 'requesting', 'succeeded', 'failed'];
        self::assertTrue(in_array($job->preparation->state, ['preparing', 'failed']));
        self::assertEquals(new MetaState(true, false), $job->preparation->metaState);
        self::assertTrue(in_array($job->preparation->requestStates['results-job'], $expectedRequestStates));
        self::assertTrue(in_array($job->preparation->requestStates['serialized-suite'], $expectedRequestStates));
        self::assertTrue(in_array($job->preparation->requestStates['machine'], $expectedRequestStates));
        self::assertTrue(in_array($job->preparation->requestStates['worker-job'], $expectedRequestStates));

        self::assertNotNull($job->components->resultsJob);
        self::assertEquals(new MetaState(false, false), $job->components->resultsJob->metaState);

        self::assertTrue(in_array(
            $job->components->resultsJob->state,
            [
                'awaiting-events',
                'started',
                'compiling',
                'compiled',
                'executing',
                'executed',
                'ended',
            ]
        ));
        self::assertNull($job->components->resultsJob->endState);

        self::assertNull($job->components->serializedSuite);
        self::assertNull($job->components->machine);

        self::assertSame('pending', $job->components->workerJob->state);
        self::assertEquals(new MetaState(false, false), $job->components->workerJob->metaState);
        self::assertEquals(
            [
                'compilation' => new WorkerJobComponent('pending', new MetaState(false, false)),
                'execution' => new WorkerJobComponent('pending', new MetaState(false, false)),
                'event_delivery' => new WorkerJobComponent('pending', new MetaState(false, false)),
            ],
            $job->components->workerJob->componentStates,
        );

        self::assertNotEmpty($job->serviceRequests);
    }

    /**
     * @return array<mixed>
     */
    public static function getSuccessDataProvider(): array
    {
        return [
            'maximum duration 600' => [
                'maximumDurationInSeconds' => 600,
            ],
        ];
    }
}
