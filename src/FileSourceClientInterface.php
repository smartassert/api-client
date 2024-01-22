<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\Source\FileSource;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\Http\HttpClientException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedContentTypeException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedDataException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;

interface FileSourceClientInterface
{
    /**
     * @param non-empty-string $apiKey
     *
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws ErrorException
     */
    public function create(string $apiKey, string $label): FileSource;

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
     *
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws ErrorException
     */
    public function update(string $apiKey, string $id, string $label): FileSource;

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
     *
     * @return non-empty-string[]
     *
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws ErrorException
     */
    public function list(string $apiKey, string $id): array;
}
