<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpException extends \Exception
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly ResponseInterface $response,
    ) {
        parent::__construct('', $response->getStatusCode());
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
