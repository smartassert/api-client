<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
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
     */
    public function createUserFrontendToken(string $userIdentifier, string $password): RefreshableToken
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/user/frontend/token/create')))
                ->withPayload(
                    new UrlEncodedPayload([
                        'user-identifier' => $userIdentifier,
                        'password' => $password,
                    ])
                )
        );

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
    public function verifyUserFrontendToken(string $token): User
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('GET', $this->createUrl('/user/frontend/token/verify')))
                ->withAuthentication(
                    new BearerAuthentication($token)
                )
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
        $userData = $responseDataInspector->getArray('user');
        $userDataInspector = new ArrayInspector($userData);

        $id = $userDataInspector->getNonEmptyString('id');
        $userIdentifier = $userDataInspector->getNonEmptyString('user-identifier');

        if (null === $id || null === $userIdentifier) {
            throw InvalidModelDataException::fromJsonResponse(User::class, $response);
        }

        return new User($id, $userIdentifier);
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
}
