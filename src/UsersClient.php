<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\User\ApiKey;
use SmartAssert\ApiClient\Data\User\Token;
use SmartAssert\ApiClient\Data\User\User;
use SmartAssert\ApiClient\Exception\Http\HttpClientException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedContentTypeException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedDataException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Exception\User\AlreadyExistsException;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\ServiceClient\RequestBuilder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class UsersClient
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private HttpHandler $httpHandler,
        private RequestBuilder $requestBuilder,
    ) {
    }

    /**
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws IncompleteDataException
     */
    public function createToken(string $userIdentifier, string $password): Token
    {
        $request = $this->requestBuilder
            ->create('POST', $this->urlGenerator->generate('user_token_create'))
            ->withJsonBody(['username' => $userIdentifier, 'password' => $password])
            ->get()
        ;

        return $this->createTokenFromResponseData($this->httpHandler->getJson($request));
    }

    /**
     * @param non-empty-string $token
     *
     * @throws UnauthorizedException
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    public function verifyToken(string $token): User
    {
        $request = $this->requestBuilder
            ->create('GET', $this->urlGenerator->generate('user_token_verify'))
            ->withBearerAuthorization($token)
            ->get()
        ;

        return $this->createUser($this->httpHandler->getJson($request));
    }

    /**
     * @param non-empty-string $refreshToken
     *
     * @throws UnauthorizedException
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    public function refreshToken(string $refreshToken): Token
    {
        $request = $this->requestBuilder
            ->create('POST', $this->urlGenerator->generate('user_token_refresh'))
            ->withJsonBody(['refresh_token' => $refreshToken])
            ->get()
        ;

        return $this->createTokenFromResponseData($this->httpHandler->getJson($request));
    }

    /**
     * @param non-empty-string $adminToken
     * @param non-empty-string $userIdentifier
     * @param non-empty-string $password
     *
     * @throws UnauthorizedException
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws AlreadyExistsException
     */
    public function create(string $adminToken, string $userIdentifier, string $password): User
    {
        $request = $this->requestBuilder
            ->create('POST', $this->urlGenerator->generate('user_create'))
            ->withAuthorization($adminToken)
            ->withFormBody(['identifier' => $userIdentifier, 'password' => $password])
            ->get()
        ;

        try {
            $data = $this->httpHandler->getJson($request);
        } catch (HttpException $e) {
            if (409 === $e->getCode()) {
                throw new AlreadyExistsException($userIdentifier, $e->response);
            }

            throw $e;
        }

        return $this->createUser($data);
    }

    /**
     * @param non-empty-string $adminToken
     * @param non-empty-string $userId
     *
     * @throws UnauthorizedException
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     */
    public function revokeAllRefreshTokensForUser(string $adminToken, string $userId): void
    {
        $request = $this->requestBuilder
            ->create('POST', $this->urlGenerator->generate('user_refresh-token_revoke-all'))
            ->withAuthorization($adminToken)
            ->withFormBody(['id' => $userId])
            ->get()
        ;

        $this->httpHandler->sendRequest($request);
    }

    /**
     * @param non-empty-string $token
     * @param non-empty-string $refreshToken
     *
     * @throws UnauthorizedException
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     */
    public function revokeRefreshToken(string $token, string $refreshToken): void
    {
        $request = $this->requestBuilder
            ->create('POST', $this->urlGenerator->generate('user_refresh-token_revoke'))
            ->withBearerAuthorization($token)
            ->withFormBody(['refresh_token' => $refreshToken])
            ->get()
        ;

        $this->httpHandler->sendRequest($request);
    }

    /**
     * @param non-empty-string $token
     *
     * @throws UnauthorizedException
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    public function getApiKey(string $token): ApiKey
    {
        $request = $this->requestBuilder
            ->create('GET', $this->urlGenerator->generate('user_apikey'))
            ->withBearerAuthorization($token)
            ->get()
        ;

        $data = $this->httpHandler->getJson($request);

        $apiKey = $this->createApiKey($data);
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
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    public function getApiKeys(string $token): array
    {
        $request = $this->requestBuilder
            ->create('GET', $this->urlGenerator->generate('user_apikey_list'))
            ->withBearerAuthorization($token)
            ->get()
        ;

        $data = $this->httpHandler->getJson($request);

        $apiKeys = [];
        foreach ($data as $apiKeyData) {
            if (is_array($apiKeyData)) {
                $apiKey = $this->createApiKey($apiKeyData);
                if (null !== $apiKey) {
                    $apiKeys[] = $apiKey;
                }
            }
        }

        return $apiKeys;
    }

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    private function createTokenFromResponseData(array $data): Token
    {
        $token = $data['token'] ?? null;
        $token = is_string($token) ? trim($token) : null;
        if ('' === $token || null === $token) {
            throw new IncompleteDataException($data, 'token');
        }

        $refreshToken = $data['refresh_token'] ?? null;
        $refreshToken = is_string($refreshToken) ? trim($refreshToken) : null;
        if ('' === $refreshToken || null === $refreshToken) {
            throw new IncompleteDataException($data, 'refresh_token');
        }

        return new Token($token, $refreshToken);
    }

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    private function createUser(array $data): User
    {
        $id = $data['id'] ?? null;
        $id = is_string($id) ? trim($id) : null;
        if ('' === $id || null === $id) {
            throw new IncompleteDataException($data, 'id');
        }

        $identifier = $data['user-identifier'] ?? null;
        $identifier = is_string($identifier) ? trim($identifier) : null;
        if ('' === $identifier || null === $identifier) {
            throw new IncompleteDataException($data, 'user-identifier');
        }

        return new User($id, $identifier);
    }

    /**
     * @param array<mixed> $data
     */
    private function createApiKey(array $data): ?ApiKey
    {
        $label = $data['label'] ?? null;
        $label = is_string($label) ? trim($label) : null;
        $label = '' === $label ? null : $label;

        $key = $data['key'] ?? null;
        $key = is_string($key) ? trim($key) : null;
        if ('' === $key || null === $key) {
            return null;
        }

        return new ApiKey($label, $key);
    }
}
