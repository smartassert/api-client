<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\Results\Event;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\Results\EventFactory;
use SmartAssert\ApiClient\Request\Header\ApiKeyAuthorizationHeader;
use SmartAssert\ApiClient\Request\RequestSpecification;
use SmartAssert\ApiClient\Request\RouteRequirements;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;

readonly class ResultsEventClient
{
    public function __construct(
        private EventFactory $eventFactory,
        private HttpHandler $httpHandler,
    ) {}

    /**
     * @param non-empty-string $apiKey
     *
     * @return Event[]
     *
     * @throws ClientException
     */
    public function list(string $apiKey, string $label, ?string $reference, ?string $type): array
    {
        $requestSpecification = new RequestSpecification(
            'GET',
            new RouteRequirements(
                'results-event-list',
                [
                    'label' => $label,
                    'reference' => $reference,
                    'type' => $type,
                ]
            ),
            new ApiKeyAuthorizationHeader($apiKey),
        );

        $data = $this->httpHandler->getJson($requestSpecification);

        $events = [];
        foreach ($data as $dataIndex => $eventData) {
            if (!is_array($eventData)) {
                continue;
            }

            try {
                $event = $this->eventFactory->create($eventData);
            } catch (IncompleteDataException $e) {
                throw new ClientException(
                    $requestSpecification,
                    new IncompleteDataException($data, $dataIndex . '.' . $e->missingKey)
                );
            }

            $events[] = $event;
        }

        return $events;
    }
}
