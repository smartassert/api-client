<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

use Psr\Http\Message\RequestInterface;

class ActionException extends \Exception implements ActionExceptionInterface
{
    /**
     * @param non-empty-string $requestName
     */
    public function __construct(
        private readonly string $requestName,
        private readonly RequestInterface $request,
        int $code = 0,
    ) {
        parent::__construct('', $code);
    }

    public function getRequestName(): string
    {
        return $this->requestName;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
