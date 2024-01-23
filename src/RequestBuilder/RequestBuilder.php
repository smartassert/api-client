<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\RequestBuilder;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RequestBuilder
{
    private RequestInterface $request;

    public function __construct(
        private readonly StreamFactoryInterface $streamFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function create(
        string $method,
        RouteRequirements $routeRequirements,
        ?HeaderInterface $header = null,
        ?BodyInterface $body = null,
    ): RequestBuilder {
        $this->request = new Request(
            $method,
            $this->urlGenerator->generate($routeRequirements->name, $routeRequirements->parameters)
        );

        if ($header instanceof HeaderInterface) {
            foreach ($header->toArray() as $name => $value) {
                $this->request = $this->request->withHeader($name, $value);
            }
        }

        if ($body instanceof BodyInterface) {
            $this->request = $this->request->withHeader('content-type', $body->getContentType());
            $this->request = $this->request->withBody($this->streamFactory->createStream($body->getContent()));
        }

        return $this;
    }

    public function get(): RequestInterface
    {
        return $this->request;
    }
}
