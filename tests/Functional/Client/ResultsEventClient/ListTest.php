<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\ResultsEventClient;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Data\Results\Event;
use SmartAssert\ApiClient\Data\Results\JobStartedEvent;
use SmartAssert\ApiClient\Data\Results\LifecycleEvent;
use SmartAssert\ApiClient\Data\Results\ResourceReference;
use SmartAssert\ApiClient\Data\Results\ResourceReferenceCollection;
use SmartAssert\ApiClient\Data\Results\TestInterface;
use SmartAssert\ApiClient\Tests\Functional\Client\ClientActionThrowsIncompleteDataExceptionTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\ExpectedRequestProperties;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestAuthenticationTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestPropertiesTestTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class ListTest extends AbstractResultsEventClientTestCase
{
    use ClientActionThrowsIncompleteDataExceptionTestTrait;
    use InvalidJsonResponseExceptionDataProviderTrait;
    use NetworkErrorExceptionDataProviderTrait;
    use RequestPropertiesTestTrait;
    use RequestAuthenticationTestTrait;

    public static function clientActionThrowsExceptionDataProvider(): array
    {
        return array_merge(
            self::networkErrorExceptionDataProvider(),
            self::invalidJsonResponseExceptionDataProvider(),
        );
    }

    /**
     * @return array<mixed>
     */
    public static function incompleteResponseDataExceptionDataProvider(): array
    {
        return [
            'sequence_number missing' => [
                'payload' => [
                    [
                        'type' => 'type_1',
                        'label' => 'resource_reference_label',
                        'reference' => 'resource_reference_reference',
                    ],
                ],
                'expectedRequestName' => 'get_results-event-list',
                'expectedMissingKey' => '0.sequence_number',
            ],
            'type missing' => [
                'payload' => [
                    [
                        'sequence_number' => 1,
                        'label' => 'resource_reference_label',
                        'reference' => 'resource_reference_reference',
                    ],
                ],
                'expectedRequestName' => 'get_results-event-list',
                'expectedMissingKey' => '0.type',
            ],
            'resource reference label missing' => [
                'payload' => [
                    [
                        'sequence_number' => 1,
                        'type' => 'type_1',
                        'reference' => 'resource_reference_reference',
                    ],
                ],
                'expectedRequestName' => 'get_results-event-list',
                'expectedMissingKey' => '0.label',
            ],
            'resource reference reference missing' => [
                'payload' => [
                    [
                        'sequence_number' => 1,
                        'type' => 'type_1',
                        'label' => 'resource_reference_label',
                    ],
                ],
                'expectedRequestName' => 'get_results-event-list',
                'expectedMissingKey' => '0.reference',
            ],
            'related reference label missing' => [
                'payload' => [
                    [
                        'sequence_number' => 1,
                        'type' => 'type_1',
                        'label' => 'resource_reference_label',
                        'reference' => 'resource_reference_reference',
                        'related_references' => [
                            [
                                'reference' => 'related_reference_reference',
                            ],
                        ],
                    ],
                ],
                'expectedRequestName' => 'get_results-event-list',
                'expectedMissingKey' => '0.related_reference[0].label',
            ],
            'related reference reference missing' => [
                'payload' => [
                    [
                        'sequence_number' => 1,
                        'type' => 'type_1',
                        'label' => 'resource_reference_label',
                        'reference' => 'resource_reference_reference',
                        'related_references' => [
                            [
                                'label' => 'related_reference_label',
                            ],
                        ],
                    ],
                ],
                'expectedRequestName' => 'get_results-event-list',
                'expectedMissingKey' => '0.related_reference[0].reference',
            ],
        ];
    }

    /**
     * @param array<mixed>    $responseData
     * @param TestInterface[] $expected
     */
    #[DataProvider('listSuccessDataProvider')]
    public function testListSuccess(array $responseData, array $expected): void
    {
        $this->mockHandler->append(
            new Response(
                200,
                ['content-type' => 'application/json'],
                (string) json_encode($responseData)
            ),
        );

        $list = $this->client->list('api-key', 'job-label', null, null);

        self::assertEquals($expected, $list);
    }

    /**
     * @return array<mixed>
     */
    public static function listSuccessDataProvider(): array
    {
        return [
            'empty' => [
                'responseData' => [],
                'expected' => [],
            ],
            'single unmodelled event' => [
                'responseData' => [
                    [
                        'sequence_number' => 1,
                        'type' => 'unmodelled-type',
                        'label' => 'label',
                        'reference' => 'reference',
                    ],
                ],
                'expected' => [
                    new Event(
                        1,
                        'unmodelled-type',
                        new ResourceReference('label', 'reference'),
                        [],
                        null,
                    ),
                ],
            ],
            'job/started, 
            lifecycle/compilation-started, 
            lifecycle/compilation-completed,
            lifecycle/execution-started,
            lifecycle/execution-completed' => [
                'responseData' => [
                    [
                        'sequence_number' => 1,
                        'type' => 'job/started',
                        'label' => 'label',
                        'reference' => 'reference',
                        'body' => [
                            'tests' => [
                                'test1.yaml',
                                'test2.yaml',
                            ],
                        ],
                        'related_references' => [
                            [
                                'label' => 'test1.yaml',
                                'reference' => 'test1_reference',
                            ],
                            [
                                'label' => 'test2.yaml',
                                'reference' => 'test2_reference',
                            ],
                        ],
                    ],
                    [
                        'sequence_number' => 2,
                        'type' => 'lifecycle/compilation-started',
                        'label' => 'label',
                        'reference' => 'reference',
                        'body' => [],
                        'related_references' => [],
                    ],
                    [
                        'sequence_number' => 3,
                        'type' => 'lifecycle/compilation-completed',
                        'label' => 'label',
                        'reference' => 'reference',
                        'body' => [],
                        'related_references' => [],
                    ],
                    [
                        'sequence_number' => 4,
                        'type' => 'lifecycle/execution-started',
                        'label' => 'label',
                        'reference' => 'reference',
                        'body' => [],
                        'related_references' => [],
                    ],
                    [
                        'sequence_number' => 5,
                        'type' => 'lifecycle/execution-completed',
                        'label' => 'label',
                        'reference' => 'reference',
                        'body' => [],
                        'related_references' => [],
                    ],
                ],
                'expected' => [
                    new JobStartedEvent(
                        new Event(
                            1,
                            'job/started',
                            new ResourceReference('label', 'reference'),
                            [
                                'tests' => [
                                    'test1.yaml',
                                    'test2.yaml',
                                ],
                            ],
                            new ResourceReferenceCollection([
                                new ResourceReference('test1.yaml', 'test1_reference'),
                                new ResourceReference('test2.yaml', 'test2_reference'),
                            ]),
                        )
                    ),
                    new LifecycleEvent(
                        new Event(
                            2,
                            'lifecycle/compilation-started',
                            new ResourceReference('label', 'reference'),
                            [],
                            null,
                        )
                    ),
                    new LifecycleEvent(
                        new Event(
                            3,
                            'lifecycle/compilation-completed',
                            new ResourceReference('label', 'reference'),
                            [],
                            null,
                        )
                    ),
                    new LifecycleEvent(
                        new Event(
                            4,
                            'lifecycle/execution-started',
                            new ResourceReference('label', 'reference'),
                            [],
                            null,
                        )
                    ),
                    new LifecycleEvent(
                        new Event(
                            5,
                            'lifecycle/execution-completed',
                            new ResourceReference('label', 'reference'),
                            [],
                            null,
                        )
                    ),
                ],
            ],
        ];
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->list(
                apiKey: self::API_KEY,
                label: self::JOB_LABEL,
                reference: null,
                type: null,
            );
        };
    }

    protected function getResponseFixture(): ResponseInterface
    {
        return new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                [
                    'sequence_number' => 1,
                    'type' => 'type_1',
                    'label' => 'resource_reference_label',
                    'reference' => 'resource_reference_reference',
                ],
            ])
        );
    }

    protected function getExpectedRequestProperties(): ExpectedRequestProperties
    {
        return new ExpectedRequestProperties('GET', '/results/event/list/' . self::JOB_LABEL);
    }
}
