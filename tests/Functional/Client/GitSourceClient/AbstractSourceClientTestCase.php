<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\GitSourceClient;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\GitSourceClient;
use SmartAssert\ApiClient\Model\Source\GitSource;
use SmartAssert\ApiClient\Tests\Functional\Client\AbstractClientTestCase;
use SmartAssert\ApiClient\Tests\Functional\Client\ClientActionThrowsInvalidModelDataExceptionTestTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\ApiClient\UrlGeneratorFactory;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\ExceptionFactory\CurlExceptionFactory;
use SmartAssert\ServiceClient\ResponseFactory\ResponseFactory;

abstract class AbstractSourceClientTestCase extends AbstractClientTestCase
{
    use CommonNonSuccessResponseDataProviderTrait;
    use ClientActionThrowsInvalidModelDataExceptionTestTrait;

    protected const ID = 'id';
    protected const LABEL = 'label';
    protected const HOST_URL = 'api key';
    protected const PATH = 'api key';
    protected const HAS_CREDENTIALS = false;

    protected GitSourceClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $httpFactory = new HttpFactory();

        $this->client = new GitSourceClient(
            UrlGeneratorFactory::create('https://api.example.com'),
            new ServiceClient(
                $httpFactory,
                $httpFactory,
                $this->httpClient,
                ResponseFactory::createFactory(),
                new CurlExceptionFactory(),
            ),
            new SourceFactory(),
        );
    }

    protected function getExpectedModelClass(): string
    {
        return GitSource::class;
    }

    protected function getResponseFixture(): ResponseInterface
    {
        return new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'git_source' => [
                    'type' => 'git',
                    'id' => self::ID,
                    'label' => self::LABEL,
                    'host_url' => self::HOST_URL,
                    'path' => self::PATH,
                    'has_credentials' => self::HAS_CREDENTIALS,
                ],
            ])
        );
    }
}
