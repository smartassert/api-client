<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

use Psr\Http\Client\ClientExceptionInterface as Psr7ClientInterface;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\File\NotFoundException as FileNotFoundException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedResponseFormatException;
use SmartAssert\ApiClient\Exception\User\AlreadyExistsException;

class ClientException extends \Exception
{
    /**
     * @param non-empty-string $requestName
     */
    public function __construct(
        private readonly string $requestName,
        private readonly AlreadyExistsException|
        ErrorException|
        FileNotFoundException|
        ForbiddenException|
        HttpException|
        IncompleteDataException|
        NotFoundException|
        Psr7ClientInterface|
        UnauthorizedException|
        UnexpectedResponseFormatException $innerException,
    ) {
        parent::__construct();
    }

    /**
     * @return non-empty-string
     */
    public function getRequestName(): string
    {
        return $this->requestName;
    }

    public function getInnerException(): AlreadyExistsException|
    ErrorException|
    FileNotFoundException|
    ForbiddenException|
    HttpException|
    IncompleteDataException|
    NotFoundException|
    Psr7ClientInterface|
    UnauthorizedException|
    UnexpectedResponseFormatException
    {
        return $this->innerException;
    }
}
