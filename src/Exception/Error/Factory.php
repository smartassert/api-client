<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Error;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceRequest\Deserializer\Error\Deserializer;
use SmartAssert\ServiceRequest\Error\DuplicateObjectErrorInterface;
use SmartAssert\ServiceRequest\Error\ModifyReadOnlyEntityErrorInterface;
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
    public function createFromResponse(ResponseInterface $response): ?ErrorException
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

        $error = $this->errorDeserializer->deserialize($data);

        if ($error instanceof DuplicateObjectErrorInterface) {
            return new DuplicateObjectException($error, $response);
        }

        if ($error instanceof ModifyReadOnlyEntityErrorInterface) {
            return new ModifyReadOnlyEntityException($error, $response);
        }

        return new ErrorException($error, $response);
    }
}
