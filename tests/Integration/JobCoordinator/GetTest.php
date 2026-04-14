<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\JobCoordinator;

use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ComponentPreparation;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Machine;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\MetaState;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ResultsJob;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\SerializedSuite;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ServiceRequest;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ServiceRequestAttempt;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJob;
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

        self::assertTrue(in_array($job->preparation->state, ['preparing', 'failed']));
        self::assertEquals(new MetaState(true, false), $job->preparation->metaState);

        $resultsJob = $job->components->get('results-job');
        self::assertInstanceOf(ResultsJob::class, $resultsJob);
        self::assertEquals(new MetaState(false, false), $resultsJob->metaState);

        self::assertTrue(in_array(
            $resultsJob->state,
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
        self::assertNull($resultsJob->endState);
        self::assertEquals(new ComponentPreparation('succeeded', 'succeeded'), $resultsJob->preparation);

        $serializedSuite = $job->components->get('serialized-suite');
        self::assertInstanceOf(SerializedSuite::class, $serializedSuite);
        self::assertNull($serializedSuite->state);
        self::assertEquals(new MetaState(false, false), $serializedSuite->metaState);
        self::assertEquals(new ComponentPreparation('failed', 'failed'), $serializedSuite->preparation);
        self::assertEquals(
            [
                new ServiceRequest(
                    'serialized-suite/create',
                    [
                        new ServiceRequestAttempt('failed'),
                    ]
                ),
            ],
            $serializedSuite->serviceRequests
        );
        self::assertEquals(new ComponentPreparation('failed', 'failed'), $serializedSuite->preparation);

        $machine = $job->components->get('machine');
        self::assertInstanceOf(Machine::class, $machine);
        self::assertNull($machine->stateCategory);
        self::assertNull($machine->ipAddress);
        self::assertNull($machine->actionFailure);
        self::assertEquals(new MetaState(false, false), $machine->metaState);
        self::assertNotEmpty($machine->serviceRequests);

        $workerJob = $job->components->get('worker-job');
        self::assertInstanceOf(WorkerJob::class, $workerJob);
        self::assertSame('pending', $workerJob->state);
        self::assertEquals(new MetaState(false, false), $workerJob->metaState);
        self::assertEmpty($workerJob->serviceRequests);
        self::assertEquals(
            [
                'compilation' => new WorkerJobComponent('pending', new MetaState(false, false)),
                'execution' => new WorkerJobComponent('pending', new MetaState(false, false)),
                'event_delivery' => new WorkerJobComponent('pending', new MetaState(false, false)),
            ],
            $workerJob->componentStates,
        );
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
