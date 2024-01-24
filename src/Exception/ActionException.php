<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

use Psr\Http\Message\RequestInterface;

class ActionException extends \Exception implements ActionExceptionInterface
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        private readonly string $name,
        private readonly RequestInterface $request,
        int $code = 0,
    ) {
        parent::__construct('', $code);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
