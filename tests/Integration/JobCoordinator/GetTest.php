<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\JobCoordinator;

use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ComponentPreparation;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Job;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Machine;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\MetaState;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ResultsJob;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\SerializedSuite;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ServiceRequest;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ServiceRequestAttempt;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJob;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJobComponent;
use SmartAssert\ApiClient\Exception\ClientExceptionInterface;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use Symfony\Component\Uid\Ulid;

class GetTest extends AbstractJobCoordinatorClientTestCase
{
    private const int MICROSECONDS_PER_SECOND = 1000000;

    public function testGetUnauthorized(): void
    {
        $exception = null;
        $jobId = (string) new Ulid();

        try {
            $this->jobCoordinatorClient->get(md5((string) rand()), $jobId);
        } catch (ClientExceptionInterface $exception) {
        }

        self::assertInstanceOf(UnauthorizedException::class, $exception);
    }

    #[DataProvider('getSuccessDataProvider')]
    public function testGetSuccess(int $maximumDurationInSeconds): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $suiteId = (string) new Ulid();

        $createdJob = $this->jobCoordinatorClient->create($apiKey->key, $suiteId, $maximumDurationInSeconds);
        $job = $this->getJobAndWaitForSerializedSuiteToReachEndState($apiKey->key, $createdJob->summary->id);

        self::assertEquals(new MetaState(true, false, false), $job->metaState);
        self::assertSame($suiteId, $job->summary->suiteId);
        self::assertSame($maximumDurationInSeconds, $job->summary->maximumDurationInSeconds);

        self::assertTrue(in_array($job->preparation->state, ['preparing', 'failed']));
        self::assertEquals(new MetaState(true, false, false), $job->preparation->metaState);

        $resultsJob = $job->components->get('results-job');
        self::assertInstanceOf(ResultsJob::class, $resultsJob);
        self::assertEquals(new MetaState(false, false, true), $resultsJob->metaState);

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
        self::assertFalse($resultsJob->hasEvents);

        $serializedSuite = $job->components->get('serialized-suite');
        self::assertInstanceOf(SerializedSuite::class, $serializedSuite);
        self::assertSame('failed', $serializedSuite->state);
        self::assertEquals(new MetaState(true, false, false), $serializedSuite->metaState);
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

        self::assertNull($machine->ipAddress);
        self::assertNull($machine->actionFailure);

        $machineHasEnded = 'end' === $machine->stateCategory;
        if ($machineHasEnded) {
            self::assertSame('end', $machine->stateCategory);
            self::assertEquals(new MetaState(true, false, false), $machine->metaState);
            self::assertNull($machine->ipAddress);
            self::assertNull($machine->actionFailure);
            self::assertEquals(new MetaState(true, false, false), $machine->metaState);
            self::assertNotEmpty($machine->serviceRequests);
            self::assertEquals(new ComponentPreparation('failed', 'failed'), $machine->preparation);
        }

        if (!$machineHasEnded) {
            $machineHasServiceRequests = [] !== $machine->serviceRequests;

            self::assertNull($machine->stateCategory);
            self::assertEquals(new MetaState(false, false, true), $machine->metaState);

            if ($machineHasServiceRequests) {
                self::assertNotEmpty($machine->serviceRequests);
                self::assertTrue(
                    $this->isComponentPreparationIsOneOf(
                        [
                            new ComponentPreparation('preparing', 'requesting'),
                            new ComponentPreparation('preparing', 'halted'),
                        ],
                        $machine->preparation
                    )
                );
            }

            if (!$machineHasServiceRequests) {
                self::assertEmpty($machine->serviceRequests);
                self::assertEquals(new ComponentPreparation('pending', 'pending'), $machine->preparation);
            }
        }

        $workerJob = $job->components->get('worker-job');
        self::assertInstanceOf(WorkerJob::class, $workerJob);
        self::assertSame('pending', $workerJob->state);
        self::assertEquals(new MetaState(false, false, true), $workerJob->metaState);
        self::assertEmpty($workerJob->serviceRequests);
        self::assertEquals(
            [
                'compilation' => new WorkerJobComponent('pending', new MetaState(false, false, true)),
                'execution' => new WorkerJobComponent('pending', new MetaState(false, false, true)),
                'event_delivery' => new WorkerJobComponent('pending', new MetaState(false, false, true)),
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

    /**
     * @param ComponentPreparation[] $values
     */
    private function isComponentPreparationIsOneOf(array $values, ComponentPreparation $componentPreparation): bool
    {
        foreach ($values as $value) {
            if (
                $value->state === $componentPreparation->state
                && $value->requestState === $componentPreparation->requestState
                && (
                    null === $value->failure
                    || (
                        $value->failure->type === $componentPreparation->failure?->type
                        && $value->failure->code === $componentPreparation->failure->code
                        && $value->failure->message === $componentPreparation->failure->message
                    )
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $jobId
     */
    private function getJobAndWaitForSerializedSuiteToReachEndState(string $apiKey, string $jobId): Job
    {
        $waitThreshold = self::MICROSECONDS_PER_SECOND * 5;
        $totalWaitTime = 0;
        $period = (int) (self::MICROSECONDS_PER_SECOND * 0.5);

        $job = $this->jobCoordinatorClient->get($apiKey, $jobId);
        $serializedSuite = $job->components->get('serialized-suite');
        \assert($serializedSuite instanceof SerializedSuite);
        $has = 'failed' === $serializedSuite->state;

        while (false === $has && $totalWaitTime < $waitThreshold) {
            $totalWaitTime += $period;
            usleep($period);

            $job = $this->jobCoordinatorClient->get($apiKey, $jobId);
            $serializedSuite = $job->components->get('serialized-suite');
            \assert($serializedSuite instanceof SerializedSuite);

            $has = 'failed' === $serializedSuite->state;
        }

        if ($totalWaitTime >= $waitThreshold) {
            throw new \RuntimeException('Exceeded threshold waiting for serialized suite to end.');
        }

        return $job;
    }
}
