<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\JobCoordinatorClient;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ComponentPreparationFailure;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Exception;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Job;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Machine;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\MachineActionFailure;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJobCreationFailure;
use SmartAssert\ApiClient\Tests\Functional\Client\ClientActionThrowsIncompleteDataExceptionTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\ExpectedRequestProperties;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestAuthenticationTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestPropertiesTestTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class GetTest extends AbstractJobCoordinatorClientTestCase
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
            'id missing' => [
                'payload' => ['suite_id' => self::SUITE_ID],
                'expectedRequestName' => 'get_job-coordinator-job',
                'expectedMissingKey' => 'id',
            ],
        ];
    }

    /**
     * @param null|array<mixed> $responseMachineActionFailureData
     */
    #[DataProvider('getWithMachineActionFailureDataProvider')]
    public function testGetWithMachineActionFailure(
        ?array $responseMachineActionFailureData,
        ?MachineActionFailure $expectedMachineActionFailure,
    ): void {
        $this->getMockHandler()->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'id' => self::ID,
                'suite_id' => self::SUITE_ID,
                'maximum_duration_in_seconds' => self::MAXIMUM_DURATION_IN_SECONDS,
                'meta_state' => [
                    'ended' => false,
                    'succeeded' => false,
                ],
                'preparation' => [
                    'state' => 'requesting',
                ],
                'components' => [
                    'results-job' => [
                        'preparation' => [
                            'state' => 'pending',
                            'request_state' => 'pending',
                        ],
                        'has_events' => false,
                    ],
                    'serialized-suite' => [
                        'preparation' => [
                            'state' => 'pending',
                            'request_state' => 'pending',
                        ],
                    ],
                    'machine' => [
                        'state_category' => 'failed',
                        'ip_address' => null,
                        'action_failure' => $responseMachineActionFailureData,
                        'preparation' => [
                            'state' => 'succeeded',
                            'request_state' => 'succeeded',
                        ],
                    ],
                    'worker-job' => [
                        'state' => 'pending',
                        'preparation' => [
                            'state' => 'pending',
                            'request_state' => 'pending',
                        ],
                    ],
                ],
            ])
        ));

        $job = ($this->createClientActionCallable())();

        $machine = $job->components->get('machine');
        \assert($machine instanceof Machine);

        self::assertEquals($expectedMachineActionFailure, $machine->actionFailure);
    }

    /**
     * @return array<mixed>
     */
    public static function getWithMachineActionFailureDataProvider(): array
    {
        return [
            'without action failure' => [
                'responseMachineActionFailureData' => null,
                'expectedMachineActionFailure' => null,
            ],
            'with invalid action failure, empty action' => [
                'responseMachineActionFailureData' => [
                    'action' => '',
                    'type' => md5((string) rand()),
                    'context' => null,
                ],
                'expectedMachineActionFailure' => null,
            ],
            'with invalid action failure, empty type' => [
                'responseMachineActionFailureData' => [
                    'action' => md5((string) rand()),
                    'type' => '',
                    'context' => null,
                ],
                'expectedMachineActionFailure' => null,
            ],
            'with invalid action failure, non-array context' => [
                'responseMachineActionFailureData' => [
                    'action' => 'non-empty action',
                    'type' => 'non-empty type',
                    'context' => md5((string) rand()),
                ],
                'expectedMachineActionFailure' => new MachineActionFailure(
                    'non-empty action',
                    'non-empty type',
                    null
                ),
            ],
            'with action failure, with null context' => [
                'responseMachineActionFailureData' => [
                    'action' => 'get',
                    'type' => 'get error type',
                    'context' => null,
                ],
                'expectedMachineActionFailure' => new MachineActionFailure(
                    'get',
                    'get error type',
                    null
                ),
            ],
            'with action failure, with context' => [
                'responseMachineActionFailureData' => [
                    'action' => 'delete',
                    'type' => 'delete error type',
                    'context' => [
                        'provider' => 'provider name',
                    ],
                ],
                'expectedMachineActionFailure' => new MachineActionFailure(
                    'delete',
                    'delete error type',
                    [
                        'provider' => 'provider name',
                    ],
                ),
            ],
        ];
    }

    /**
     * @param ?array<mixed> $componentPreparationFailureData
     */
    #[DataProvider('jobComponentPreparationFailureDataDataProvider')]
    public function testGetWithResultsJobPreparationFailure(
        ?array $componentPreparationFailureData,
        ?ComponentPreparationFailure $expected,
    ): void {
        $responseData = $this->createResponseData([
            'components' => [
                'results-job' => [
                    'preparation' => [
                        'failure' => $componentPreparationFailureData,
                    ],
                ],
            ],
        ]);

        $this->getMockHandler()->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode($responseData)
        ));

        $job = ($this->createClientActionCallable())();

        self::assertEquals($expected, $job->getResultsJob()?->preparation->failure);
    }

    /**
     * @param ?array<mixed> $componentPreparationFailureData
     */
    #[DataProvider('jobComponentPreparationFailureDataDataProvider')]
    public function testGetWithSerializedSuitePreparationFailure(
        ?array $componentPreparationFailureData,
        ?ComponentPreparationFailure $expected,
    ): void {
        $responseData = $this->createResponseData([
            'components' => [
                'serialized-suite' => [
                    'preparation' => [
                        'failure' => $componentPreparationFailureData,
                    ],
                ],
            ],
        ]);

        $this->getMockHandler()->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode($responseData)
        ));

        $job = ($this->createClientActionCallable())();

        self::assertEquals($expected, $job->getSerializedSuite()?->preparation->failure);
    }

    /**
     * @param ?array<mixed> $componentPreparationFailureData
     */
    #[DataProvider('jobComponentPreparationFailureDataDataProvider')]
    public function testGetWithMachinePreparationFailure(
        ?array $componentPreparationFailureData,
        ?ComponentPreparationFailure $expected,
    ): void {
        $responseData = $this->createResponseData([
            'components' => [
                'machine' => [
                    'preparation' => [
                        'failure' => $componentPreparationFailureData,
                    ],
                ],
            ],
        ]);

        $this->getMockHandler()->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode($responseData)
        ));

        $job = ($this->createClientActionCallable())();

        self::assertEquals($expected, $job->getMachine()?->preparation->failure);
    }

    /**
     * @param ?array<mixed> $componentPreparationFailureData
     */
    #[DataProvider('jobComponentPreparationFailureDataDataProvider')]
    public function testGetWithWorkerJobPreparationFailure(
        ?array $componentPreparationFailureData,
        ?ComponentPreparationFailure $expected,
    ): void {
        $responseData = $this->createResponseData([
            'components' => [
                'worker-job' => [
                    'preparation' => [
                        'failure' => $componentPreparationFailureData,
                    ],
                ],
            ],
        ]);

        $this->getMockHandler()->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode($responseData)
        ));

        $job = ($this->createClientActionCallable())();

        self::assertEquals($expected, $job->getWorkerJob()?->preparation->failure);
    }

    /**
     * @return array<mixed>
     */
    public static function jobComponentPreparationFailureDataDataProvider(): array
    {
        return [
            'no failure' => [
                'componentPreparationFailureData' => [],
                'expected' => null,
            ],
            'has failure' => [
                'componentPreparationFailureData' => [
                    'type' => 'network',
                    'code' => 6,
                    'message' => 'hostname lookup failed',
                ],
                'expected' => new ComponentPreparationFailure(
                    'network',
                    6,
                    'hostname lookup failed',
                ),
            ],
        ];
    }

    /**
     * @param ?array<mixed> $responseWorkerJobCreationFailureData
     */
    #[DataProvider('getWithWorkerJobCreationFailureDataProvider')]
    public function testGetWithWorkerJobCreationFailure(
        ?array $responseWorkerJobCreationFailureData,
        ?WorkerJobCreationFailure $expected,
    ): void {
        $this->getMockHandler()->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'id' => self::ID,
                'suite_id' => self::SUITE_ID,
                'maximum_duration_in_seconds' => self::MAXIMUM_DURATION_IN_SECONDS,
                'meta_state' => [
                    'ended' => false,
                    'succeeded' => false,
                ],
                'preparation' => [
                    'state' => 'not-relevant',
                ],
                'components' => [
                    'results-job' => [
                        'preparation' => [
                            'state' => 'pending',
                            'request_state' => 'pending',
                        ],
                    ],
                    'serialized-suite' => [
                        'preparation' => [
                            'state' => 'pending',
                            'request_state' => 'pending',
                        ],
                    ],
                    'machine' => [
                        'preparation' => [
                            'state' => 'pending',
                            'request_state' => 'pending',
                        ],
                    ],
                    'worker-job' => [
                        'state' => 'pending',
                        'preparation' => [
                            'state' => 'pending',
                            'request_state' => 'pending',
                        ],
                        'creation_failure' => $responseWorkerJobCreationFailureData,
                    ],
                ],
                'service_requests' => [],
            ])
        ));

        $job = ($this->createClientActionCallable())();

        self::assertEquals($expected, $job->getWorkerJob()?->creationFailure);
    }

    /**
     * @return array<mixed>
     */
    public static function getWithWorkerJobCreationFailureDataProvider(): array
    {
        return [
            'no worker job creation failure' => [
                'responseWorkerJobCreationFailureData' => null,
                'expected' => null,
            ],
            'has worker job creation failure; data empty' => [
                'responseWorkerJobCreationFailureData' => [],
                'expected' => null,
            ],
            'has worker job creation failure; array lacking "stage"' => [
                'responseWorkerJobCreationFailureData' => [
                    'exception' => [
                        'class' => self::class,
                        'code' => 123,
                        'message' => 'message content',
                    ],
                ],
                'expected' => new WorkerJobCreationFailure(
                    '',
                    new Exception(self::class, 123, 'message content'),
                ),
            ],
            'has worker job creation failure; array lacking "exception"' => [
                'responseWorkerJobCreationFailureData' => [
                    'stage' => 'stage-content',
                ],
                'expected' => new WorkerJobCreationFailure(
                    'stage-content',
                    new Exception('', 0, ''),
                ),
            ],
            'has worker job creation failure; array lacking "exception.class"' => [
                'responseWorkerJobCreationFailureData' => [
                    'stage' => 'stage-content',
                    'exception' => [
                        'code' => 123,
                        'message' => 'message content',
                    ],
                ],
                'expected' => new WorkerJobCreationFailure(
                    'stage-content',
                    new Exception('', 123, 'message content'),
                ),
            ],
            'has worker job creation failure; array lacking "exception.code"' => [
                'responseWorkerJobCreationFailureData' => [
                    'stage' => 'stage-content',
                    'exception' => [
                        'class' => self::class,
                        'message' => 'message content',
                    ],
                ],
                'expected' => new WorkerJobCreationFailure(
                    'stage-content',
                    new Exception(self::class, 0, 'message content'),
                ),
            ],
            'has worker job creation failure; array lacking "exception.message"' => [
                'responseWorkerJobCreationFailureData' => [
                    'stage' => 'stage-content',
                    'exception' => [
                        'class' => self::class,
                        'code' => 123,
                    ],
                ],
                'expected' => new WorkerJobCreationFailure(
                    'stage-content',
                    new Exception(self::class, 123, ''),
                ),
            ],
            'has worker job creation failure; all parts present"' => [
                'responseWorkerJobCreationFailureData' => [
                    'stage' => 'stage-content',
                    'exception' => [
                        'class' => self::class,
                        'code' => 123,
                        'message' => 'message content',
                    ],
                ],
                'expected' => new WorkerJobCreationFailure(
                    'stage-content',
                    new Exception(self::class, 123, 'message content'),
                ),
            ],
        ];
    }

    /**
     * @return callable(): Job
     */
    protected function createClientActionCallable(): callable
    {
        return function (): Job {
            return $this->client->get(self::API_KEY, self::ID);
        };
    }

    protected function getResponseFixture(): ResponseInterface
    {
        return new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'id' => self::ID,
                'suite_id' => self::SUITE_ID,
                'maximum_duration_in_seconds' => self::MAXIMUM_DURATION_IN_SECONDS,
                'created_at' => (int) new \DateTimeImmutable()->format('U'),
                'meta_state' => [
                    'ended' => false,
                    'succeeded' => false,
                    'pending' => true,
                ],
                'preparation' => [
                    'state' => 'requesting',
                    'meta_state' => [
                        'ended' => false,
                        'succeeded' => false,
                        'pending' => true,
                    ],
                ],
                'components' => [
                    'results-job' => [
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                            'pending' => true,
                        ],
                        'preparation' => [
                            'state' => 'pending',
                            'request_state' => 'pending',
                        ],
                    ],
                    'serialized-suite' => [
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                            'pending' => true,
                        ],
                        'preparation' => [
                            'state' => 'pending',
                            'request_state' => 'pending',
                        ],
                    ],
                    'machine' => [
                        'state_category' => 'idle',
                        'ip_address' => null,
                        'action_failure' => null,
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                            'pending' => true,
                        ],
                        'preparation' => [
                            'state' => 'pending',
                            'request_state' => 'pending',
                        ],
                    ],
                    'worker-job' => [
                        'state' => 'pending',
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                            'pending' => true,
                        ],
                        'preparation' => [
                            'state' => 'pending',
                            'request_state' => 'pending',
                        ],
                        'creation_failure' => [
                            'stage' => 'stage-content',
                            'exception' => [
                                'class' => self::class,
                                'code' => 123,
                                'message' => 'message content',
                            ],
                        ],
                        'components' => [
                            'compilation' => [
                                'state' => 'pending',
                                'meta_state' => [
                                    'ended' => false,
                                    'succeeded' => false,
                                    'pending' => true,
                                ],
                            ],
                            'execution' => [
                                'state' => 'pending',
                                'meta_state' => [
                                    'ended' => false,
                                    'succeeded' => false,
                                    'pending' => true,
                                ],
                            ],
                            'event_delivery' => [
                                'state' => 'pending',
                                'meta_state' => [
                                    'ended' => false,
                                    'succeeded' => false,
                                    'pending' => true,
                                ],
                            ],
                        ],
                    ],
                ],
                'service_requests' => [],
            ])
        );
    }

    protected function getExpectedRequestProperties(): ExpectedRequestProperties
    {
        return new ExpectedRequestProperties('GET', '/job-coordinator/' . self::ID);
    }

    /**
     * @param array<mixed> $modifications
     *
     * @return array<mixed>
     */
    private function createResponseData(array $modifications): array
    {
        $template = [
            'id' => self::ID,
            'suite_id' => self::SUITE_ID,
            'maximum_duration_in_seconds' => self::MAXIMUM_DURATION_IN_SECONDS,
            'meta_state' => [
                'ended' => false,
                'succeeded' => false,
            ],
            'preparation' => [
                'state' => 'not-relevant',
            ],
            'components' => [
                'results-job' => [
                    'preparation' => [
                        'state' => 'pending',
                        'request_state' => 'pending',
                    ],
                ],
                'serialized-suite' => [
                    'preparation' => [
                        'state' => 'pending',
                        'request_state' => 'pending',
                    ],
                ],
                'machine' => [
                    'preparation' => [
                        'state' => 'pending',
                        'request_state' => 'pending',
                    ],
                ],
                'worker-job' => [
                    'state' => 'pending',
                    'preparation' => [
                        'state' => 'pending',
                        'request_state' => 'pending',
                    ],
                ],
            ],
        ];

        return array_merge_recursive($template, $modifications);
    }
}
