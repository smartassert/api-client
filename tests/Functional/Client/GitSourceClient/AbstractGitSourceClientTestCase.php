<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\GitSourceClient;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\GitSourceClient;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\Tests\Functional\Client\AbstractClientTestCase;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\ApiClient\UrlGeneratorFactory;

abstract class AbstractGitSourceClientTestCase extends AbstractClientTestCase
{
    use CommonNonSuccessResponseDataProviderTrait;

    protected const ID = 'id';
    protected const LABEL = 'label';
    protected const HOST_URL = 'api key';
    protected const PATH = 'api key';
    protected const HAS_CREDENTIALS = false;

    protected GitSourceClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new GitSourceClient(
            new SourceFactory(),
            new HttpHandler(
                $this->httpClient,
                $this->exceptionFactory,
                new HttpFactory(),
                UrlGeneratorFactory::create('https://api.example.com'),
            ),
        );
    }

    /**
     * @return array<mixed>
     */
    public static function incompleteDataExceptionDataProvider(): array
    {
        return [
            'id missing' => [
                'payload' => [
                    'label' => md5((string) rand()),
                    'host_url' => md5((string) rand()),
                    'path' => md5((string) rand()),
                ],
                'expectedMissingKey' => 'id',
            ],
            'label missing' => [
                'payload' => [
                    'id' => md5((string) rand()),
                    'host_url' => md5((string) rand()),
                    'path' => md5((string) rand()),
                ],
                'expectedMissingKey' => 'label',
            ],
            'host_url missing' => [
                'payload' => [
                    'id' => md5((string) rand()),
                    'label' => md5((string) rand()),
                    'path' => md5((string) rand()),
                ],
                'expectedMissingKey' => 'host_url',
            ],
            'path missing' => [
                'payload' => [
                    'id' => md5((string) rand()),
                    'label' => md5((string) rand()),
                    'host_url' => md5((string) rand()),
                ],
                'expectedMissingKey' => 'path',
            ],
        ];
    }

    protected function getResponseFixture(): ResponseInterface
    {
        return new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'type' => 'git',
                'id' => self::ID,
                'label' => self::LABEL,
                'host_url' => self::HOST_URL,
                'path' => self::PATH,
                'has_credentials' => self::HAS_CREDENTIALS,
            ])
        );
    }

    protected function getExpectedAuthorizationHeader(): string
    {
        return 'Bearer ' . self::API_KEY;
    }
}
