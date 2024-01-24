<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Error;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceRequest\Deserializer\Error\Deserializer;
use SmartAssert\ServiceRequest\Error\ErrorInterface;
use SmartAssert\ServiceRequest\Exception\ErrorDeserializationException;
use SmartAssert\ServiceRequest\Exception\UnknownErrorClassException;

readonly class Factory
{
    public function __construct(
        private Deserializer $errorDeserializer,
    ) {
    }

    /**
     * @throws ErrorDeserializationException
     * @throws UnknownErrorClassException
     */
    public function createFromResponse(ResponseInterface $response): ?ErrorInterface
    {
        if ($response->getStatusCode() < 400) {
            return null;
        }

        if ('application/json' !== $response->getHeaderLine('content-type')) {
            return null;
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $response->getBody()->rewind();

        if (!is_array($data)) {
            return null;
        }

        return $this->errorDeserializer->deserialize($data);
    }
}
