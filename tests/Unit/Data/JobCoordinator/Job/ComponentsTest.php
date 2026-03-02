<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Unit\Data\JobCoordinator\Job;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Components;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Machine;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\MetaState;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ResultsJob;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\SerializedSuite;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJob;

class ComponentsTest extends TestCase
{
    #[DataProvider('filterByMetaStateDataProvider')]
    public function testFilterByMetaState(
        Components $components,
        MetaState $metaState,
        Components $expected
    ): void {
        self::assertEquals($expected, $components->filterByMetaState($metaState));
    }

    /**
     * @return array<mixed>
     */
    public static function filterByMetaStateDataProvider(): array
    {
        $endedSucceededMachine = new Machine(
            'state-category',
            null,
            null,
            new MetaState(true, true),
        );

        $endedSucceededWorkerJob = new WorkerJob(
            'state',
            new MetaState(true, true),
            [],
        );

        $endedSucceededResultsJob = new ResultsJob(
            'state',
            'end-state',
            new MetaState(true, true),
        );

        $endedSucceededSerializedSuite = new SerializedSuite(
            'state',
            new MetaState(true, true),
        );

        return [
            'no matching components' => [
                'components' => new Components([
                    'machine' => new Machine(
                        'state-category',
                        null,
                        null,
                        new MetaState(false, false),
                    ),
                    'worker-job' => new WorkerJob(
                        'state',
                        new MetaState(false, false),
                        [],
                    ),
                ]),
                'metaState' => new MetaState(true, true),
                'expected' => new Components([]),
            ],
            'single matching component' => [
                'components' => new Components([
                    'machine' => $endedSucceededMachine,
                    'worker-job' => new WorkerJob(
                        'state',
                        new MetaState(false, false),
                        [],
                    ),
                ]),
                'metaState' => new MetaState(true, true),
                'expected' => new Components([
                    'machine' => $endedSucceededMachine,
                ]),
            ],
            'multiple matching component' => [
                'components' => new Components([
                    'machine' => $endedSucceededMachine,
                    'worker-job' => $endedSucceededWorkerJob,
                ]),
                'metaState' => new MetaState(true, true),
                'expected' => new Components([
                    'machine' => $endedSucceededMachine,
                    'worker-job' => $endedSucceededWorkerJob,
                ]),
            ],
            'all matching components' => [
                'components' => new Components([
                    'results-job' => $endedSucceededResultsJob,
                    'serialized-suite' => $endedSucceededSerializedSuite,
                    'machine' => $endedSucceededMachine,
                    'worker-job' => $endedSucceededWorkerJob,
                ]),
                'metaState' => new MetaState(true, true),
                'expected' => new Components([
                    'results-job' => $endedSucceededResultsJob,
                    'serialized-suite' => $endedSucceededSerializedSuite,
                    'machine' => $endedSucceededMachine,
                    'worker-job' => $endedSucceededWorkerJob,
                ]),
            ],
        ];
    }
}
