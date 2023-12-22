<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use PHPUnit\Framework\TestCase;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\FileSourceClient;
use SmartAssert\ApiClient\GitSourceClient;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\UrlGeneratorFactory;
use SmartAssert\ApiClient\UsersClient;
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
        $httpClient = new HttpClient();

        $httpHandler = new HttpHandler($httpClient);

        self::$urlGenerator = UrlGeneratorFactory::create('http://localhost:9089');

        self::$usersClient = new UsersClient(self::$urlGenerator, $httpHandler);

        self::$fileSourceClient = new FileSourceClient(self::$urlGenerator, new SourceFactory(), $httpHandler);
        self::$gitSourceClient = new GitSourceClient(self::$urlGenerator, new SourceFactory(), $httpHandler);
    }
}
