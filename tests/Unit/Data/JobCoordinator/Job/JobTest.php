<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Unit\Data\JobCoordinator\Job;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Components;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Job;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Machine;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\MetaState;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Preparation;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ResultsJob;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\SerializedSuite;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Summary;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJob;

class JobTest extends TestCase
{
    #[DataProvider('getResultsJobDataProvider')]
    public function testGetResultsJob(Job $job, ?ResultsJob $expected): void
    {
        self::assertSame($expected, $job->getResultsJob());
    }

    /**
     * @return array<mixed>
     */
    public static function getResultsJobDataProvider(): array
    {
        $resultsJob = new ResultsJob(
            'state',
            null,
            new MetaState(false, false),
        );

        $machine = new Machine(
            'state-category',
            null,
            null,
            new MetaState(false, false),
        );

        return [
            'no components' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([]),
                    [],
                ),
                'expected' => null,
            ],
            'no results job component' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([
                        'machine' => $machine,
                    ]),
                    [],
                ),
                'expected' => null,
            ],
            'incorrectly-named results job component' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([
                        'results-job' => $machine,
                    ]),
                    [],
                ),
                'expected' => null,
            ],
            'has results job component' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([
                        'results-job' => $resultsJob,
                    ]),
                    [],
                ),
                'expected' => $resultsJob,
            ],
        ];
    }

    #[DataProvider('getSerializedSuiteDataProvider')]
    public function testGetSerializedSuite(Job $job, ?SerializedSuite $expected): void
    {
        self::assertSame($expected, $job->getSerializedSuite());
    }

    /**
     * @return array<mixed>
     */
    public static function getSerializedSuiteDataProvider(): array
    {
        $resultsJob = new ResultsJob(
            'state',
            null,
            new MetaState(false, false),
        );

        $serializedSuite = new SerializedSuite(
            'state',
            new MetaState(false, false),
        );

        return [
            'no components' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([]),
                    [],
                ),
                'expected' => null,
            ],
            'no serialized suite component' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([
                        'results-job' => $resultsJob,
                    ]),
                    [],
                ),
                'expected' => null,
            ],
            'incorrectly-named serialized suite component' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([
                        'serialized-suite' => $resultsJob,
                    ]),
                    [],
                ),
                'expected' => null,
            ],
            'has serialized suite component' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([
                        'serialized-suite' => $serializedSuite,
                    ]),
                    [],
                ),
                'expected' => $serializedSuite,
            ],
        ];
    }

    #[DataProvider('getMachineDataProvider')]
    public function testGetMachine(Job $job, ?Machine $expected): void
    {
        self::assertSame($expected, $job->getMachine());
    }

    /**
     * @return array<mixed>
     */
    public static function getMachineDataProvider(): array
    {
        $resultsJob = new ResultsJob(
            'state',
            null,
            new MetaState(false, false),
        );

        $machine = new Machine(
            'state-category',
            null,
            null,
            new MetaState(false, false),
        );

        return [
            'no components' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([]),
                    [],
                ),
                'expected' => null,
            ],
            'no machine component' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([
                        'results-job' => $resultsJob,
                    ]),
                    [],
                ),
                'expected' => null,
            ],
            'incorrectly-named machine component' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([
                        'machine' => $resultsJob,
                    ]),
                    [],
                ),
                'expected' => null,
            ],
            'has machine component' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([
                        'machine' => $machine,
                    ]),
                    [],
                ),
                'expected' => $machine,
            ],
        ];
    }

    #[DataProvider('getWorkerJobDataProvider')]
    public function testGetWorkerJob(Job $job, ?WorkerJob $expected): void
    {
        self::assertSame($expected, $job->getWorkerJob());
    }

    /**
     * @return array<mixed>
     */
    public static function getWorkerJobDataProvider(): array
    {
        $resultsJob = new ResultsJob(
            'state',
            null,
            new MetaState(false, false),
        );

        $workerJob = new WorkerJob(
            'state',
            new MetaState(false, false),
            []
        );

        return [
            'no components' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([]),
                    [],
                ),
                'expected' => null,
            ],
            'no worker job component' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([
                        'results-job' => $resultsJob,
                    ]),
                    [],
                ),
                'expected' => null,
            ],
            'incorrectly-named worker job component' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([
                        'worker-job' => $resultsJob,
                    ]),
                    [],
                ),
                'expected' => null,
            ],
            'has worker job component' => [
                'job' => new Job(
                    new Summary('id', 'suiteId', 600),
                    new Preparation(
                        'state',
                        new MetaState(false, false),
                        []
                    ),
                    new MetaState(false, false),
                    new Components([
                        'worker-job' => $workerJob,
                    ]),
                    [],
                ),
                'expected' => $workerJob,
            ],
        ];
    }
}
