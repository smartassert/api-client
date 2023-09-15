<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Exception\UserAlreadyExistsException;
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

readonly class Client
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
    public function createUserToken(string $userIdentifier, string $password): RefreshableToken
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
    public function verifyUserToken(string $token): User
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
    public function refreshUserToken(string $refreshToken): RefreshableToken
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
    public function createUser(string $adminToken, string $userIdentifier, string $password): User
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
}
