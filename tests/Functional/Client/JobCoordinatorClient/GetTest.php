<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\JobCoordinatorClient;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Job;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\MachineActionFailure;
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
                'preparation' => [
                    'state' => 'requesting',
                ],
                'results-job' => [],
                'serialized-suite' => [],
                'machine' => [
                    'state_category' => 'failed',
                    'ip_address' => null,
                    'action_failure' => $responseMachineActionFailureData,
                ],
                'worker-job' => [
                    'state' => 'pending',
                ],
                'service_requests' => [],
            ])
        ));

        $job = ($this->createClientActionCallable())();

        self::assertEquals($expectedMachineActionFailure, $job->components->machine?->actionFailure);
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
                ],
                'preparation' => [
                    'state' => 'requesting',
                    'meta_state' => [
                        'ended' => false,
                        'succeeded' => false,
                    ],
                ],
                'results-job' => null,
                'serialized-suite' => null,
                'machine' => [
                    'state_category' => 'idle',
                    'ip_address' => null,
                    'action_failure' => null,
                    'meta_state' => [
                        'ended' => false,
                        'succeeded' => false,
                    ],
                ],
                'worker-job' => [
                    'state' => 'pending',
                    'meta_state' => [
                        'ended' => false,
                        'succeeded' => false,
                    ],
                    'components' => [
                        'compilation' => [
                            'state' => 'pending',
                            'meta_state' => [
                                'ended' => false,
                                'succeeded' => false,
                            ],
                        ],
                        'execution' => [
                            'state' => 'pending',
                            'meta_state' => [
                                'ended' => false,
                                'succeeded' => false,
                            ],
                        ],
                        'event_delivery' => [
                            'state' => 'pending',
                            'meta_state' => [
                                'ended' => false,
                                'succeeded' => false,
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
}
