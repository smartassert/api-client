<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\GitSourceClient;

use SmartAssert\ApiClient\Tests\Functional\Client\ClientActionThrowsIncompleteDataExceptionTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\ExpectedRequestProperties;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestAuthenticationTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestPropertiesTestTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class UpdateTest extends AbstractGitSourceClientTestCase
{
    use ClientActionThrowsIncompleteDataExceptionTestTrait;
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

    /**
     * @return array<mixed>
     */
    public static function incompleteResponseDataExceptionDataProvider(): array
    {
        return [
            'host_url missing' => [
                'payload' => [
                    'type' => 'git',
                    'id' => self::ID,
                    'label' => self::LABEL,
                    'path' => self::PATH,
                    'has_credentials' => self::HAS_CREDENTIALS,
                ],
                'expectedRequestName' => 'put_git-source',
                'expectedMissingKey' => 'host_url',
            ],
        ];
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
