<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

use Psr\Http\Client\ClientExceptionInterface as Psr7ClientInterface;
use SmartAssert\ApiClient\Exception\Factory\IncompleteDataException;
use SmartAssert\ApiClient\Exception\File\NotFoundException as FileNotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedResponseFormatException;
use SmartAssert\ApiClient\Exception\User\AlreadyExistsException;
use SmartAssert\ApiClient\Request\RequestSpecification;

class ClientException extends \Exception implements ClientExceptionInterface
{
    public function __construct(
        private readonly RequestSpecification $requestSpecification,
        private readonly FileNotFoundException|
        IncompleteDataException|
        Psr7ClientInterface|
        UnexpectedResponseFormatException $innerException,
    ) {
        parent::__construct();
    }

    public function getRequestSpecification(): RequestSpecification
    {
        return $this->requestSpecification;
    }

    public function getInnerException(): FileNotFoundException|
    IncompleteDataException|
    Psr7ClientInterface|
    UnexpectedResponseFormatException
    {
        return $this->innerException;
    }
}
