<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

use Psr\Http\Client\ClientExceptionInterface as Psr7ClientInterface;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\Factory\IncompleteDataException;
use SmartAssert\ApiClient\Exception\File\NotFoundException as FileNotFoundException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedResponseFormatException;
use SmartAssert\ApiClient\Exception\User\AlreadyExistsException;
use SmartAssert\ApiClient\Request\RequestSpecification;

class ClientException extends \Exception implements ClientExceptionInterface
{
    public function __construct(
        private readonly RequestSpecification $requestSpecification,
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

    public function getRequestSpecification(): RequestSpecification
    {
        return $this->requestSpecification;
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
