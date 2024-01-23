<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\RequestBuilder;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class RequestBuilder
{
    public function __construct(
        private StreamFactoryInterface $streamFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function create(RequestSpecification $requestSpecification): RequestInterface
    {
        $routeRequirements = $requestSpecification->routeRequirements;

        $request = new Request(
            $requestSpecification->method,
            $this->urlGenerator->generate($routeRequirements->name, $routeRequirements->parameters)
        );

        $header = $requestSpecification->header;
        if ($header instanceof HeaderInterface) {
            foreach ($header->toArray() as $name => $value) {
                $request = $request->withHeader($name, $value);
            }
        }

        $body = $requestSpecification->body;
        if ($body instanceof BodyInterface) {
            $request = $request->withHeader('content-type', $body->getContentType());
            $request = $request->withBody($this->streamFactory->createStream($body->getContent()));
        }

        return $request;
    }
}
