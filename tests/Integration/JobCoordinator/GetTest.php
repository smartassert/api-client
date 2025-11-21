<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\JobCoordinator;

use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ServiceRequest;
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
        \assert('' !== $jobId);

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
        \assert('' !== $suiteId);

        $createdJob = $this->jobCoordinatorClient->create($apiKey->key, $suiteId, $maximumDurationInSeconds);
        sleep(1);

        $job = $this->jobCoordinatorClient->get($apiKey->key, $createdJob->summary->id);

        self::assertSame($suiteId, $job->summary->suiteId);
        self::assertSame($maximumDurationInSeconds, $job->summary->maximumDurationInSeconds);

        $expectedRequestStates = ['pending', 'requesting', 'succeeded', 'failed'];
        self::assertTrue(in_array($job->preparation->state, ['preparing', 'failed']));
        self::assertTrue(in_array($job->preparation->requestStates['results_job'], $expectedRequestStates));
        self::assertTrue(in_array($job->preparation->requestStates['serialized_suite'], $expectedRequestStates));
        self::assertTrue(in_array($job->preparation->requestStates['machine'], $expectedRequestStates));
        self::assertTrue(in_array($job->preparation->requestStates['worker_job'], $expectedRequestStates));

        self::assertTrue(in_array(
            $job->resultsJob->state,
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
        self::assertNull($job->resultsJob->endState);

        self::assertNull($job->serializedSuite->state);

        self::assertNull($job->machine->stateCategory);
        self::assertNull($job->machine->ipAddress);

        self::assertSame('pending', $job->workerJob->state);
        self::assertFalse($job->workerJob->isEndState);
        self::assertEquals(
            [
                'compilation' => new WorkerJobComponent('pending', false),
                'execution' => new WorkerJobComponent('pending', false),
                'event_delivery' => new WorkerJobComponent('pending', false),
            ],
            $job->workerJob->componentStates,
        );

        self::assertIsArray($job->serviceRequests);
        self::assertTrue(count($job->serviceRequests) > 0);

        foreach ($job->serviceRequests as $serviceRequest) {
            self::assertInstanceOf(ServiceRequest::class, $serviceRequest);
        }
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
