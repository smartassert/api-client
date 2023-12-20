<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\GitSourceClient;

use SmartAssert\ApiClient\Tests\Functional\Client\ExpectedRequestProperties;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestAuthenticationTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestPropertiesTestTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\FooInvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\FooNetworkErrorExceptionDataProviderTrait;

class UpdateTest extends AbstractGitSourceClientTestCase
{
    use FooInvalidJsonResponseExceptionDataProviderTrait;
    use FooNetworkErrorExceptionDataProviderTrait;
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
            $this->client->update(self::API_KEY, self::ID, self::LABEL, self::HOST_URL, self::PATH, null);
        };
    }

    protected function getExpectedRequestProperties(): ExpectedRequestProperties
    {
        return new ExpectedRequestProperties('PUT', '/source/git-source/' . self::ID);
    }
}
