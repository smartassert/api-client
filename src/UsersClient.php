<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use GuzzleHttp\Psr7\Request as HttpRequest;
use SmartAssert\ApiClient\Data\User\ApiKey;
use SmartAssert\ApiClient\Data\User\Token;
use SmartAssert\ApiClient\Data\User\User;
use SmartAssert\ApiClient\Exception\UserAlreadyExistsException;
use SmartAssert\ApiClient\FooException\Http\HttpClientException;
use SmartAssert\ApiClient\FooException\Http\HttpException;
use SmartAssert\ApiClient\FooException\Http\NotFoundException;
use SmartAssert\ApiClient\FooException\Http\UnauthorizedException as FooUnauthorizedException;
use SmartAssert\ApiClient\FooException\Http\UnexpectedContentTypeException;
use SmartAssert\ApiClient\FooException\Http\UnexpectedDataException;
use SmartAssert\ApiClient\FooException\IncompleteDataException;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class UsersClient
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private HttpHandler $httpHandler,
    ) {
    }

    /**
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     * @throws FooUnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws IncompleteDataException
     */
    public function createToken(string $userIdentifier, string $password): Token
    {
        return $this->doTokenAction('user_token_create', ['username' => $userIdentifier, 'password' => $password]);
    }

    /**
     * @param non-empty-string $token
     *
     * @throws FooUnauthorizedException
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    public function verifyToken(string $token): User
    {
        $request = new HttpRequest(
            'GET',
            $this->urlGenerator->generate('user_token_verify'),
            [
                'authorization' => 'Bearer ' . $token,
            ]
        );

        return $this->createUser($this->httpHandler->getJson($request));
    }

    /**
     * @param non-empty-string $refreshToken
     *
     * @throws FooUnauthorizedException
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    public function refreshToken(string $refreshToken): Token
    {
        return $this->doTokenAction('user_token_refresh', ['refresh_token' => $refreshToken]);
    }

    /**
     * @param non-empty-string $adminToken
     * @param non-empty-string $userIdentifier
     * @param non-empty-string $password
     *
     * @throws FooUnauthorizedException
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws UserAlreadyExistsException
     */
    public function create(string $adminToken, string $userIdentifier, string $password): User
    {
        $request = new HttpRequest(
            'POST',
            $this->urlGenerator->generate('user_create'),
            [
                'authorization' => $adminToken,
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query([
                'identifier' => $userIdentifier,
                'password' => $password,
            ])
        );

        try {
            $data = $this->httpHandler->getJson($request);
        } catch (HttpException $e) {
            if (409 === $e->getCode()) {
                throw new UserAlreadyExistsException($userIdentifier, $e->response);
            }

            throw $e;
        }

        return $this->createUser($data);
    }

    /**
     * @param non-empty-string $adminToken
     * @param non-empty-string $userId
     *
     * @throws FooUnauthorizedException
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     */
    public function revokeAllRefreshTokensForUser(string $adminToken, string $userId): void
    {
        $request = new HttpRequest(
            'POST',
            $this->urlGenerator->generate('user_refresh-token_revoke-all'),
            [
                'authorization' => $adminToken,
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query([
                'id' => $userId,
            ])
        );

        $this->httpHandler->sendRequest($request);
    }

    /**
     * @param non-empty-string $token
     * @param non-empty-string $refreshToken
     *
     * @throws FooUnauthorizedException
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     */
    public function revokeRefreshToken(string $token, string $refreshToken): void
    {
        $request = new HttpRequest(
            'POST',
            $this->urlGenerator->generate('user_refresh-token_revoke'),
            [
                'authorization' => 'Bearer ' . $token,
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query([
                'refresh_token' => $refreshToken,
            ])
        );

        $this->httpHandler->sendRequest($request);
    }

    /**
     * @param non-empty-string $token
     *
     * @throws FooUnauthorizedException
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    public function getApiKey(string $token): ApiKey
    {
        $request = new HttpRequest(
            'GET',
            $this->urlGenerator->generate('user_apikey'),
            [
                'authorization' => 'Bearer ' . $token,
            ]
        );

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
     * @throws FooUnauthorizedException
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    public function getApiKeys(string $token): array
    {
        $request = new HttpRequest(
            'GET',
            $this->urlGenerator->generate('user_apikey_list'),
            [
                'authorization' => 'Bearer ' . $token,
            ]
        );

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
     * @param array<mixed> $payload
     *
     * @throws FooUnauthorizedException
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    private function doTokenAction(string $route, array $payload): Token
    {
        $request = new HttpRequest(
            'POST',
            $this->urlGenerator->generate($route),
            [
                'content-type' => 'application/json',
            ],
            (string) json_encode($payload)
        );

        return $this->createTokenFromResponseData($this->httpHandler->getJson($request));
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
