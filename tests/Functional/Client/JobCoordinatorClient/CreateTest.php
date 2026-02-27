<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\JobCoordinatorClient;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Tests\Functional\Client\ClientActionThrowsIncompleteDataExceptionTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\ExpectedRequestProperties;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestAuthenticationTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestPropertiesTestTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class CreateTest extends AbstractJobCoordinatorClientTestCase
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
                'expectedRequestName' => 'post_job-coordinator-job',
                'expectedMissingKey' => 'id',
            ],
        ];
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->create(self::API_KEY, self::SUITE_ID, self::MAXIMUM_DURATION_IN_SECONDS);
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
                'machine' => null,
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
        return new ExpectedRequestProperties('POST', '/job-coordinator/' . self::SUITE_ID);
    }
}
