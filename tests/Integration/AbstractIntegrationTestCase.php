<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\FileSourceClient;
use SmartAssert\ApiClient\GitSourceClient;
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
    protected static UsersClient $usersClient;
    protected static FileSourceClient $fileSourceClient;
    protected static GitSourceClient $gitSourceClient;

    public static function setUpBeforeClass(): void
    {
        self::$urlGenerator = UrlGeneratorFactory::create('http://localhost:9089');

        self::$usersClient = new UsersClient(self::$urlGenerator, self::createServiceClient());
        self::$fileSourceClient = new FileSourceClient(
            self::$urlGenerator,
            self::createServiceClient(),
            new SourceFactory()
        );
        self::$gitSourceClient = new GitSourceClient(
            self::$urlGenerator,
            self::createServiceClient(),
            new SourceFactory()
        );
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
