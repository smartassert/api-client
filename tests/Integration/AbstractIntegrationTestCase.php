<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\Factory\User\TokenFactory;
use SmartAssert\ApiClient\FileSourceClient;
use SmartAssert\ApiClient\GitSourceClient;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\ServiceClient\RequestBuilder;
use SmartAssert\ApiClient\UrlGeneratorFactory;
use SmartAssert\ApiClient\UsersClient;
use SmartAssert\ServiceRequest\Deserializer\Error\BadRequestErrorDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Error\Deserializer as ErrorDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Error\DuplicateObjectErrorDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Error\ErrorFieldDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Error\ModifyReadOnlyEntityDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Error\StorageErrorDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Field\Deserializer as FieldDeserializer;
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

    protected static ErrorDeserializer $errorDeserializer;

    public static function setUpBeforeClass(): void
    {
        $errorFieldDeserializer = new ErrorFieldDeserializer(new FieldDeserializer());

        self::$errorDeserializer = new ErrorDeserializer([
            new BadRequestErrorDeserializer($errorFieldDeserializer),
            new DuplicateObjectErrorDeserializer($errorFieldDeserializer),
            new ModifyReadOnlyEntityDeserializer(),
            new StorageErrorDeserializer(),
        ]);

        $exceptionFactory = new ExceptionFactory(self::$errorDeserializer);

        $httpClient = new HttpClient();
        $httpHandler = new HttpHandler($httpClient, $exceptionFactory);
        $requestBuilder = new RequestBuilder(new HttpFactory());

        self::$urlGenerator = UrlGeneratorFactory::create('http://localhost:9089');

        self::$usersClient = new UsersClient(
            self::$urlGenerator,
            $httpHandler,
            $requestBuilder,
            new TokenFactory(),
        );

        self::$fileSourceClient = new FileSourceClient(
            self::$urlGenerator,
            new SourceFactory(),
            $httpHandler,
            $requestBuilder
        );
        self::$gitSourceClient = new GitSourceClient(
            self::$urlGenerator,
            new SourceFactory(),
            $httpHandler,
            $requestBuilder,
        );
    }
}
