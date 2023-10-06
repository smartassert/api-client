<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Exception\UserAlreadyExistsException;
use SmartAssert\ApiClient\Model\ApiKey;
use SmartAssert\ApiClient\Model\RefreshableToken;
use SmartAssert\ApiClient\Model\User;
use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Payload\UrlEncodedPayload;
use SmartAssert\ServiceClient\Request;
use SmartAssert\ServiceClient\Response\JsonResponse;
use SmartAssert\ServiceClient\Response\ResponseInterface;

readonly class UsersClient
{
    public function __construct(
        private string $baseUrl,
        private ServiceClient $serviceClient,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws CurlExceptionInterface
     * @throws RequestExceptionInterface
     * @throws NonSuccessResponseException
     * @throws InvalidResponseTypeException
     * @throws InvalidResponseDataException
     * @throws InvalidModelDataException
     * @throws UnauthorizedException
     */
    public function createToken(string $userIdentifier, string $password): RefreshableToken
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/user/token/create')))
                ->withPayload(
                    new UrlEncodedPayload([
                        'user-identifier' => $userIdentifier,
                        'password' => $password,
                    ])
                )
        );

        return $this->handleRefreshableTokenResponse($response);
    }

    /**
     * @param non-empty-string $token
     *
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws CurlExceptionInterface
     * @throws RequestExceptionInterface
     * @throws UnauthorizedException
     * @throws NonSuccessResponseException
     * @throws InvalidResponseTypeException
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     */
    public function verifyToken(string $token): User
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('GET', $this->createUrl('/user/token/verify')))
                ->withAuthentication(
                    new BearerAuthentication($token)
                )
        );

        return $this->handleUserResponse($response);
    }

    /**
     * @param non-empty-string $refreshToken
     *
     * @throws ClientExceptionInterface
     * @throws CurlExceptionInterface
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NetworkExceptionInterface
     * @throws NonSuccessResponseException
     * @throws RequestExceptionInterface
     * @throws UnauthorizedException
     */
    public function refreshToken(string $refreshToken): RefreshableToken
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/user/token/refresh')))
                ->withAuthentication(new BearerAuthentication($refreshToken))
        );

        return $this->handleRefreshableTokenResponse($response);
    }

    /**
     * @param non-empty-string $adminToken
     * @param non-empty-string $userIdentifier
     * @param non-empty-string $password
     *
     * @throws ClientExceptionInterface
     * @throws CurlExceptionInterface
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NetworkExceptionInterface
     * @throws NonSuccessResponseException
     * @throws RequestExceptionInterface
     * @throws UnauthorizedException
     * @throws UserAlreadyExistsException
     */
    public function create(string $adminToken, string $userIdentifier, string $password): User
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/user/create')))
                ->withAuthentication(new BearerAuthentication($adminToken))
                ->withPayload(new UrlEncodedPayload(['user-identifier' => $userIdentifier, 'password' => $password]))
        );

        if (409 === $response->getStatusCode()) {
            throw new UserAlreadyExistsException($userIdentifier, $response->getHttpResponse());
        }

        return $this->handleUserResponse($response);
    }

    /**
     * @param non-empty-string $adminToken
     * @param non-empty-string $userId
     *
     * @throws ClientExceptionInterface
     * @throws CurlExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws NonSuccessResponseException
     * @throws RequestExceptionInterface
     * @throws UnauthorizedException
     */
    public function revokeAllRefreshTokensForUser(string $adminToken, string $userId): void
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/user/refresh_token/revoke-all')))
                ->withAuthentication(new BearerAuthentication($adminToken))
                ->withPayload(new UrlEncodedPayload(['id' => $userId]))
        );

        if (401 === $response->getStatusCode()) {
            throw new UnauthorizedException();
        }

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }
    }

    /**
     * @param non-empty-string $token
     * @param non-empty-string $refreshToken
     *
     * @throws ClientExceptionInterface
     * @throws CurlExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws NonSuccessResponseException
     * @throws RequestExceptionInterface
     * @throws UnauthorizedException
     */
    public function revokeRefreshToken(string $token, string $refreshToken): void
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/user/refresh_token/revoke')))
                ->withAuthentication(new BearerAuthentication($token))
                ->withPayload(new UrlEncodedPayload(['refresh_token' => $refreshToken]))
        );

        if (401 === $response->getStatusCode()) {
            throw new UnauthorizedException();
        }

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }
    }

    /**
     * @param non-empty-string $token
     *
     * @throws ClientExceptionInterface
     * @throws CurlExceptionInterface
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NetworkExceptionInterface
     * @throws NonSuccessResponseException
     * @throws RequestExceptionInterface
     * @throws UnauthorizedException
     */
    public function getApiKey(string $token): ApiKey
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('GET', $this->createUrl('/user/apikey/')))
                ->withAuthentication(new BearerAuthentication($token))
        );

        if (401 === $response->getStatusCode()) {
            throw new UnauthorizedException();
        }

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $responseDataInspector = new ArrayInspector($response->getData());
        $modelData = $responseDataInspector->getArray('api_key');

        $apiKey = $this->createApiKey(new ArrayInspector($modelData));
        if (null === $apiKey) {
            throw InvalidModelDataException::fromJsonResponse(ApiKey::class, $response);
        }

        return $apiKey;
    }

    /**
     * @param non-empty-string $token
     *
     * @return ApiKey[]
     *
     * @throws ClientExceptionInterface
     * @throws CurlExceptionInterface
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NetworkExceptionInterface
     * @throws NonSuccessResponseException
     * @throws RequestExceptionInterface
     * @throws UnauthorizedException
     */
    public function getApiKeys(string $token): array
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('GET', $this->createUrl('/user/apikey/list')))
                ->withAuthentication(new BearerAuthentication($token))
        );

        if (401 === $response->getStatusCode()) {
            throw new UnauthorizedException();
        }

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $responseDataInspector = new ArrayInspector($response->getData());
        $collectionData = $responseDataInspector->getArray('api_keys');

        $apiKeys = [];

        foreach ($collectionData as $modelData) {
            if (is_array($modelData)) {
                $apiKey = $this->createApiKey(new ArrayInspector($modelData));
                if (null !== $apiKey) {
                    $apiKeys[] = $apiKey;
                }
            }
        }

        return $apiKeys;
    }

    /**
     * @param non-empty-string $path
     *
     * @return non-empty-string
     */
    private function createUrl(string $path): string
    {
        return rtrim($this->baseUrl, '/') . $path;
    }

    /**
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NonSuccessResponseException
     * @throws UnauthorizedException
     */
    private function handleRefreshableTokenResponse(ResponseInterface $response): RefreshableToken
    {
        if (401 === $response->getStatusCode()) {
            throw new UnauthorizedException();
        }

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $responseDataInspector = new ArrayInspector($response->getData());
        $modelData = $responseDataInspector->getArray('refreshable_token');

        $modelDataInspector = new ArrayInspector($modelData);
        $token = $modelDataInspector->getNonEmptyString('token');
        $refreshToken = $modelDataInspector->getNonEmptyString('refresh_token');

        if (null === $token || null === $refreshToken) {
            throw InvalidModelDataException::fromJsonResponse(RefreshableToken::class, $response);
        }

        return new RefreshableToken($token, $refreshToken);
    }

    /**
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NonSuccessResponseException
     * @throws UnauthorizedException
     */
    private function handleUserResponse(ResponseInterface $response): User
    {
        if (401 === $response->getStatusCode()) {
            throw new UnauthorizedException();
        }

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $responseDataInspector = new ArrayInspector($response->getData());
        $userData = $responseDataInspector->getArray('user');
        $userDataInspector = new ArrayInspector($userData);

        $id = $userDataInspector->getNonEmptyString('id');
        $userIdentifier = $userDataInspector->getNonEmptyString('user-identifier');

        if (null === $id || null === $userIdentifier) {
            throw InvalidModelDataException::fromJsonResponse(User::class, $response);
        }

        return new User($id, $userIdentifier);
    }

    private function createApiKey(ArrayInspector $data): ?ApiKey
    {
        $label = $data->getNonEmptyString('label');
        $key = $data->getNonEmptyString('key');

        if (null === $key) {
            return null;
        }

        return new ApiKey($label, $key);
    }
}
