<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\GitSourceClient;

use SmartAssert\ApiClient\Tests\Functional\Client\ExpectedRequestProperties;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestAuthenticationTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestPropertiesTestTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class GetTest extends AbstractSourceClientTestCase
{
    use InvalidJsonResponseExceptionDataProviderTrait;
    use NetworkErrorExceptionDataProviderTrait;
    use RequestPropertiesTestTrait;
    use RequestAuthenticationTestTrait;

    public static function clientActionThrowsExceptionDataProvider(): array
    {
        return array_merge(
            self::networkErrorExceptionDataProvider(),
            self::invalidJsonResponseExceptionDataProvider(),
        );
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->get(self::API_KEY, self::ID);
        };
    }

    protected function getExpectedRequestProperties(): ExpectedRequestProperties
    {
        return new ExpectedRequestProperties('GET', '/git-source/' . self::ID);
    }
}
