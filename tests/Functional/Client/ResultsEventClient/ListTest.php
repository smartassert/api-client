<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\ResultsEventClient;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
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
