<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\Results\Event;
use SmartAssert\ApiClient\Data\Results\EventInterface;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\Factory\IncompleteDataException;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedResponseFormatException;
use SmartAssert\ApiClient\Exception\HttpClientException;
use SmartAssert\ApiClient\Exception\IncompleteResponseDataException;
use SmartAssert\ApiClient\Exception\NotFoundException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
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
     * @return EventInterface[]
     *
     * @throws ErrorException
     * @throws ForbiddenException
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws IncompleteResponseDataException
     * @throws UnexpectedResponseFormatException
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
                throw new IncompleteResponseDataException(
                    $requestSpecification,
                    new IncompleteDataException($data, $dataIndex . '.' . $e->missingKey)
                );
            }

            $events[] = $event;
        }

        return $events;
    }
}
