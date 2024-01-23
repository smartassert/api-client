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

        return $this;
    }

    public function withAuthorization(string $authorization): RequestBuilder
    {
        $this->request = $this->request->withHeader('authorization', $authorization);

        return $this;
    }

    public function withBearerAuthorization(string $authorization): RequestBuilder
    {
        return $this->withAuthorization('Bearer ' . $authorization);
    }

    /**
     * @param non-empty-string[] $contentTypes
     */
    public function withAcceptableContentTypes(array $contentTypes): RequestBuilder
    {
        $this->request = $this->request->withHeader('accept', implode(', ', $contentTypes));

        return $this;
    }

    public function withBody(string $contentType, string $content): RequestBuilder
    {
        $this->request = $this->request->withHeader('content-type', $contentType);
        $this->request = $this->request->withBody($this->streamFactory->createStream($content));

        return $this;
    }

    /**
     * @param array<mixed> $data
     */
    public function withFormBody(array $data): RequestBuilder
    {
        return $this->withBody('application/x-www-form-urlencoded', http_build_query($data));
    }

    /**
     * @param array<mixed> $data
     */
    public function withJsonBody(array $data): RequestBuilder
    {
        return $this->withBody('application/json', (string) json_encode($data));
    }

    public function get(): RequestInterface
    {
        return $this->request;
    }
}
