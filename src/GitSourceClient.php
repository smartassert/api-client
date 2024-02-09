<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\Source\GitSource;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\Request\Body\FormBody;
use SmartAssert\ApiClient\Request\Header\ApiKeyAuthorizationHeader;
use SmartAssert\ApiClient\Request\RequestSpecification;
use SmartAssert\ApiClient\Request\RouteRequirements;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;

readonly class GitSourceClient
{
    public function __construct(
        private SourceFactory $sourceFactory,
        private HttpHandler $httpHandler,
    ) {
    }

    /**
     * @param non-empty-string $apiKey
     *
     * @throws ClientException
     */
    public function create(
        string $apiKey,
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
    ): GitSource {
        return $this->doAction('POST', $apiKey, $label, $hostUrl, $path, $credentials);
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
     *
     * @throws ClientException
     */
    public function update(
        string $apiKey,
        string $id,
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
    ): GitSource {
        return $this->doAction('PUT', $apiKey, $label, $hostUrl, $path, $credentials, $id);
    }

    /**
     * @throws ClientException
     */
    private function doAction(
        string $method,
        string $apiKey,
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
        ?string $id = null,
    ): GitSource {
        $requestSpecification = new RequestSpecification(
            $method,
            new RouteRequirements('git-source', ['sourceId' => $id]),
            new ApiKeyAuthorizationHeader($apiKey),
            new FormBody([
                'label' => $label,
                'host-url' => $hostUrl,
                'path' => $path,
                'credentials' => $credentials,
            ])
        );

        try {
            return $this->sourceFactory->createGitSource(
                $this->httpHandler->getJson($requestSpecification)
            );
        } catch (IncompleteDataException $e) {
            throw new ClientException($requestSpecification->getName(), $e);
        }
    }
}
