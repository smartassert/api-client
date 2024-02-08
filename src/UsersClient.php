<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\User\ApiKey;
use SmartAssert\ApiClient\Data\User\Token;
use SmartAssert\ApiClient\Data\User\User;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Exception\User\AlreadyExistsException;
use SmartAssert\ApiClient\Factory\User\ApiKeyFactory;
use SmartAssert\ApiClient\Factory\User\TokenFactory;
use SmartAssert\ApiClient\Factory\User\UserFactory;
use SmartAssert\ApiClient\Request\Body\FormBody;
use SmartAssert\ApiClient\Request\Body\JsonBody;
use SmartAssert\ApiClient\Request\Header\AuthorizationHeader;
use SmartAssert\ApiClient\Request\Header\BearerAuthorizationHeader;
use SmartAssert\ApiClient\Request\RequestSpecification;
use SmartAssert\ApiClient\Request\RouteRequirements;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;

readonly class UsersClient
{
    public function __construct(
        private HttpHandler $httpHandler,
        private TokenFactory $tokenFactory,
        private UserFactory $userFactory,
        private ApiKeyFactory $apiKeyFactory,
    ) {
    }

    /**
     * @throws ClientException
     */
    public function createToken(string $userIdentifier, string $password): Token
    {
        $requestSpecification = new RequestSpecification(
            'POST',
            new RouteRequirements('user_token_create'),
            null,
            new JsonBody(['username' => $userIdentifier, 'password' => $password])
        );

        try {
            return $this->tokenFactory->create(
                $this->httpHandler->getJson($requestSpecification)
            );
        } catch (IncompleteDataException $e) {
            throw new ClientException($requestSpecification->getName(), $e);
        }
    }

    /**
     * @param non-empty-string $token
     *
     * @throws ClientException
     */
    public function verifyToken(string $token): User
    {
        $requestSpecification = new RequestSpecification(
            'GET',
            new RouteRequirements('user_token_verify'),
            new BearerAuthorizationHeader($token),
        );

        try {
            return $this->userFactory->create(
                $this->httpHandler->getJson($requestSpecification)
            );
        } catch (IncompleteDataException $e) {
            throw new ClientException($requestSpecification->getName(), $e);
        }
    }

    /**
     * @param non-empty-string $refreshToken
     *
     * @throws ClientException
     */
    public function refreshToken(string $refreshToken): Token
    {
        $requestSpecification = new RequestSpecification(
            'POST',
            new RouteRequirements('user_token_refresh'),
            null,
            new JsonBody(['refresh_token' => $refreshToken])
        );

        try {
            return $this->tokenFactory->create(
                $this->httpHandler->getJson($requestSpecification)
            );
        } catch (IncompleteDataException $e) {
            throw new ClientException($requestSpecification->getName(), $e);
        }
    }

    /**
     * @param non-empty-string $adminToken
     * @param non-empty-string $userIdentifier
     * @param non-empty-string $password
     *
     * @throws ClientException
     */
    public function create(string $adminToken, string $userIdentifier, string $password): User
    {
        $requestSpecification = new RequestSpecification(
            'POST',
            new RouteRequirements('user_create'),
            new AuthorizationHeader($adminToken),
            new FormBody(['identifier' => $userIdentifier, 'password' => $password])
        );

        try {
            $data = $this->httpHandler->getJson($requestSpecification);
        } catch (ClientException $e) {
            $innerException = $e->getInnerException();

            if ($innerException instanceof HttpException && 409 === $innerException->getCode()) {
                throw new ClientException(
                    $e->getRequestName(),
                    new AlreadyExistsException($userIdentifier, $innerException->getResponse())
                );
            }

            throw $e;
        }

        try {
            return $this->userFactory->create($data);
        } catch (IncompleteDataException $e) {
            throw new ClientException($requestSpecification->getName(), $e);
        }
    }

    /**
     * @param non-empty-string $adminToken
     * @param non-empty-string $userId
     *
     * @throws ClientException
     */
    public function revokeAllRefreshTokensForUser(string $adminToken, string $userId): void
    {
        $this->httpHandler->sendRequest(new RequestSpecification(
            'POST',
            new RouteRequirements('user_refresh-token_revoke-all'),
            new AuthorizationHeader($adminToken),
            new FormBody(['id' => $userId])
        ));
    }

    /**
     * @param non-empty-string $token
     * @param non-empty-string $refreshToken
     *
     * @throws ClientException
     */
    public function revokeRefreshToken(string $token, string $refreshToken): void
    {
        $this->httpHandler->sendRequest(new RequestSpecification(
            'POST',
            new RouteRequirements('user_refresh-token_revoke'),
            new BearerAuthorizationHeader($token),
            new FormBody(['refresh_token' => $refreshToken])
        ));
    }

    /**
     * @param non-empty-string $token
     *
     * @throws ClientException
     */
    public function getApiKey(string $token): ApiKey
    {
        $requestSpecification = new RequestSpecification(
            'GET',
            new RouteRequirements('user_apikey'),
            new BearerAuthorizationHeader($token),
        );

        $data = $this->httpHandler->getJson($requestSpecification);

        $apiKey = $this->apiKeyFactory->create($data);
        if (null === $apiKey) {
            throw new ClientException(
                $requestSpecification->getName(),
                new IncompleteDataException($data, 'key')
            );
        }

        return $apiKey;
    }

    /**
     * @param non-empty-string $token
     *
     * @return ApiKey[]
     *
     * @throws ClientException
     */
    public function getApiKeys(string $token): array
    {
        $data = $this->httpHandler->getJson(new RequestSpecification(
            'GET',
            new RouteRequirements('user_apikey_list'),
            new BearerAuthorizationHeader($token),
        ));

        $apiKeys = [];
        foreach ($data as $apiKeyData) {
            if (is_array($apiKeyData)) {
                $apiKey = $this->apiKeyFactory->create($apiKeyData);
                if (null !== $apiKey) {
                    $apiKeys[] = $apiKey;
                }
            }
        }

        return $apiKeys;
    }
}
