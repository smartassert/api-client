<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Request\Header;

class ApiKeyAuthorizationHeader extends HeaderCollection
{
    public function __construct(string $apiKey)
    {
        parent::__construct([
            new Header('translate-authorization-to', 'api-token'),
            new BearerAuthorizationHeader($apiKey)
        ]);
    }
}
