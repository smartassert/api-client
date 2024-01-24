<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\User\ApiKey;
use SmartAssert\ApiClient\Data\User\Token;
use SmartAssert\ApiClient\Data\User\User;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\Http\FailedRequestException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedContentTypeException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedDataException;
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
     * @throws FailedRequestException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws IncompleteDataException
     * @throws ErrorException
     */
    public function createToken(string $userIdentifier, string $password): Token
    {
        return $this->tokenFactory->create(
            $this->httpHandler->getJson(new RequestSpecification(
                'POST',
                new RouteRequirements('user_token_create'),
                null,
                new JsonBody(['username' => $userIdentifier, 'password' => $password])
            ))
        );
    }

    /**
     * @param non-empty-string $token
     *
     * @throws UnauthorizedException
     * @throws FailedRequestException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws ErrorException
     */
    public function verifyToken(string $token): User
    {
        return $this->userFactory->create(
            $this->httpHandler->getJson(new RequestSpecification(
                'GET',
                new RouteRequirements('user_token_verify'),
                new BearerAuthorizationHeader($token),
            ))
        );
    }

    /**
     * @param non-empty-string $refreshToken
     *
     * @throws UnauthorizedException
     * @throws FailedRequestException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws ErrorException
     */
    public function refreshToken(string $refreshToken): Token
    {
        return $this->tokenFactory->create(
            $this->httpHandler->getJson(new RequestSpecification(
                'POST',
                new RouteRequirements('user_token_refresh'),
                null,
                new JsonBody(['refresh_token' => $refreshToken])
            ))
        );
    }

    /**
     * @param non-empty-string $adminToken
     * @param non-empty-string $userIdentifier
     * @param non-empty-string $password
     *
     * @throws UnauthorizedException
     * @throws FailedRequestException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws AlreadyExistsException
     * @throws ErrorException
     */
    public function create(string $adminToken, string $userIdentifier, string $password): User
    {
        try {
            $data = $this->httpHandler->getJson(new RequestSpecification(
                'POST',
                new RouteRequirements('user_create'),
                new AuthorizationHeader($adminToken),
                new FormBody(['identifier' => $userIdentifier, 'password' => $password])
            ));
        } catch (HttpException $e) {
            if (409 === $e->getCode()) {
                throw new AlreadyExistsException($userIdentifier, $e->getResponse());
            }

            throw $e;
        }

        return $this->userFactory->create($data);
    }

    /**
     * @param non-empty-string $adminToken
     * @param non-empty-string $userId
     *
     * @throws UnauthorizedException
     * @throws FailedRequestException
     * @throws HttpException
     * @throws NotFoundException
     * @throws ErrorException
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
     * @throws UnauthorizedException
     * @throws FailedRequestException
     * @throws HttpException
     * @throws NotFoundException
     * @throws ErrorException
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
     * @throws UnauthorizedException
     * @throws FailedRequestException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws ErrorException
     */
    public function getApiKey(string $token): ApiKey
    {
        $data = $this->httpHandler->getJson(new RequestSpecification(
            'GET',
            new RouteRequirements('user_apikey'),
            new BearerAuthorizationHeader($token),
        ));

        $apiKey = $this->apiKeyFactory->create($data);
        if (null === $apiKey) {
            throw new IncompleteDataException($data, 'key');
        }

        return $apiKey;
    }

    /**
     * @param non-empty-string $token
     *
     * @return ApiKey[]
     *
     * @throws UnauthorizedException
     * @throws FailedRequestException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws ErrorException
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
