<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Unit\Data\Results;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\ApiClient\Data\Results\Event;
use SmartAssert\ApiClient\Data\Results\JobStartedEvent;
use SmartAssert\ApiClient\Data\Results\ResourceReference;
use SmartAssert\ApiClient\Data\Results\ResourceReferenceCollection;
use SmartAssert\ApiClient\Data\Results\Test;
use SmartAssert\ApiClient\Data\Results\TestInterface;

class JobStartedEventTest extends TestCase
{
    /**
     * @param TestInterface[] $expected
     */
    #[DataProvider('getTestsDataProvider')]
    public function testGetTests(JobStartedEvent $event, array $expected): void
    {
        self::assertEquals($expected, $event->getTests());
    }

    /**
     * @return array<mixed>
     */
    public static function getTestsDataProvider(): array
    {
        return [
            'no test collection' => [
                'event' => new JobStartedEvent(
                    new Event(
                        1,
                        'job/started',
                        new ResourceReference(
                            'job-label',
                            'job-reference',
                        ),
                        [],
                        null,
                    ),
                ),
                'expected' => [],
            ],
            'empty test collection' => [
                'event' => new JobStartedEvent(
                    new Event(
                        1,
                        'job/started',
                        new ResourceReference(
                            'job-label',
                            'job-reference',
                        ),
                        [
                            'tests' => [],
                        ],
                        null,
                    ),
                ),
                'expected' => [],
            ],
            'single test with no related reference' => [
                'event' => new JobStartedEvent(
                    new Event(
                        1,
                        'job/started',
                        new ResourceReference(
                            'job-label',
                            'job-reference',
                        ),
                        [
                            'tests' => [
                                'test1.yaml',
                            ],
                        ],
                        null,
                    ),
                ),
                'expected' => [],
            ],
            'single test with related reference' => [
                'event' => new JobStartedEvent(
                    new Event(
                        1,
                        'job/started',
                        new ResourceReference(
                            'job-label',
                            'job-reference',
                        ),
                        [
                            'tests' => [
                                'test1.yaml',
                            ],
                        ],
                        new ResourceReferenceCollection([
                            new ResourceReference('test1.yaml', 'test1-reference'),
                        ]),
                    ),
                ),
                'expected' => [
                    new Test(
                        new ResourceReference('test1.yaml', 'test1-reference'),
                    ),
                ],
            ],
            'multiple tests, some with related references and some without' => [
                'event' => new JobStartedEvent(
                    new Event(
                        1,
                        'job/started',
                        new ResourceReference(
                            'job-label',
                            'job-reference',
                        ),
                        [
                            'tests' => [
                                'test1.yaml',
                                'test2.yaml',
                                'test3.yaml',
                            ],
                        ],
                        new ResourceReferenceCollection([
                            new ResourceReference('test1.yaml', 'test1-reference'),
                            new ResourceReference('test3.yaml', 'test3-reference'),
                        ]),
                    ),
                ),
                'expected' => [
                    new Test(
                        new ResourceReference('test1.yaml', 'test1-reference'),
                    ),
                    new Test(
                        new ResourceReference('test3.yaml', 'test3-reference'),
                    ),
                ],
            ],
        ];
    }
}
