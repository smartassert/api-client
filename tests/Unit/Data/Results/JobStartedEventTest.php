<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Unit\Data\Results;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\ApiClient\Data\Results\Event;
use SmartAssert\ApiClient\Data\Results\JobMetadata;
use SmartAssert\ApiClient\Data\Results\JobStartedEvent;
use SmartAssert\ApiClient\Data\Results\ResourceReference;
use SmartAssert\ApiClient\Data\Results\ResourceReferenceCollection;

class JobStartedEventTest extends TestCase
{
    public function testGetJob(): void
    {
        $eventResourceReference = new ResourceReference(
            'job-label',
            'job-reference',
        );

        $event = new JobStartedEvent(
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
        );

        $job = $event->getJobMetadata();

        self::assertEquals(new JobMetadata($eventResourceReference), $job);
        self::assertEquals('job-label', $job->getLabel());
        self::assertEquals($eventResourceReference, $job->getResourceReference());
    }

    #[DataProvider('getTestsDataProvider')]
    public function testGetTestReferences(JobStartedEvent $event, ResourceReferenceCollection $expected): void
    {
        self::assertEquals($expected, $event->getTestReferences());
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
                'expected' => new ResourceReferenceCollection([]),
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
                'expected' => new ResourceReferenceCollection([]),
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
                'expected' => new ResourceReferenceCollection([]),
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
                'expected' => new ResourceReferenceCollection([
                    new ResourceReference('test1.yaml', 'test1-reference'),
                ]),
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
                'expected' => new ResourceReferenceCollection([
                    new ResourceReference('test1.yaml', 'test1-reference'),
                    new ResourceReference('test3.yaml', 'test3-reference'),
                ]),
            ],
        ];
    }
}
