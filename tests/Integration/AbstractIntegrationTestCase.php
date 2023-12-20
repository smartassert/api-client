<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\FileSourceClient;
use SmartAssert\ApiClient\GitSourceClient;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\UrlGeneratorFactory;
use SmartAssert\ApiClient\UsersClient;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\ExceptionFactory\CurlExceptionFactory;
use SmartAssert\ServiceClient\ResponseFactory\ResponseFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractIntegrationTestCase extends TestCase
{
    protected const USER1_EMAIL = 'user1@example.com';
    protected const USER1_PASSWORD = 'password';
    protected const USER2_EMAIL = 'user1@example.com';
    protected const USER2_PASSWORD = 'password';

    protected static UrlGeneratorInterface $urlGenerator;
    protected static UrlGeneratorInterface $fooUrlGenerator;
    protected static UsersClient $usersClient;
    protected static FileSourceClient $fileSourceClient;
    protected static GitSourceClient $gitSourceClient;

    public static function setUpBeforeClass(): void
    {
        $httpClient = new HttpClient();

        $httpHandler = new HttpHandler($httpClient);

        self::$urlGenerator = UrlGeneratorFactory::create('http://localhost:9089');
        self::$fooUrlGenerator = UrlGeneratorFactory::create('http://localhost:9093');

        self::$usersClient = new UsersClient(self::$fooUrlGenerator, $httpHandler);

        self::$fileSourceClient = new FileSourceClient(self::$fooUrlGenerator, new SourceFactory(), $httpHandler);
        self::$gitSourceClient = new GitSourceClient(self::$fooUrlGenerator, new SourceFactory(), $httpHandler);
    }

    protected static function createServiceClient(): ServiceClient
    {
        $httpFactory = new HttpFactory();

        return new ServiceClient(
            $httpFactory,
            $httpFactory,
            new HttpClient(),
            ResponseFactory::createFactory(),
            new CurlExceptionFactory(),
        );
    }
}
