<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\ApiClient\Data\User\ApiKey;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\Factory\User\ApiKeyFactory;
use SmartAssert\ApiClient\Factory\User\TokenFactory;
use SmartAssert\ApiClient\Factory\User\UserFactory;
use SmartAssert\ApiClient\FileSourceClient;
use SmartAssert\ApiClient\GitSourceClient;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\SourceClient;
use SmartAssert\ApiClient\UrlGeneratorFactory;
use SmartAssert\ApiClient\UsersClient;
use SmartAssert\ServiceRequest\Deserializer\Error\BadRequestErrorDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Error\Deserializer as ErrorDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Error\DuplicateObjectErrorDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Error\ErrorParameterDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Error\ModifyReadOnlyEntityDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Error\StorageErrorDeserializer;
use SmartAssert\ServiceRequest\Deserializer\Parameter\Deserializer as ParameterDeserializer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractIntegrationTestCase extends TestCase
{
    protected const USER1_EMAIL = 'user1@example.com';
    protected const USER1_PASSWORD = 'password';
    protected const USER2_EMAIL = 'user2@example.com';
    protected const USER2_PASSWORD = 'password';
    protected static UrlGeneratorInterface $urlGenerator;
    protected static UsersClient $usersClient;
    protected static FileSourceClient $fileSourceClient;
    protected static GitSourceClient $gitSourceClient;
    protected static SourceClient $sourceClient;

    protected static ErrorDeserializer $errorDeserializer;

    public static function setUpBeforeClass(): void
    {
        $errorFieldDeserializer = new ErrorParameterDeserializer(new ParameterDeserializer());

        self::$errorDeserializer = new ErrorDeserializer([
            new BadRequestErrorDeserializer($errorFieldDeserializer),
            new DuplicateObjectErrorDeserializer($errorFieldDeserializer),
            new ModifyReadOnlyEntityDeserializer(),
            new StorageErrorDeserializer(),
        ]);

        $exceptionFactory = new ExceptionFactory(self::$errorDeserializer);

        self::$urlGenerator = UrlGeneratorFactory::create('http://localhost:9089');

        $httpClient = new HttpClient();
        $httpHandler = new HttpHandler($httpClient, $exceptionFactory, new HttpFactory(), self::$urlGenerator);

        self::$usersClient = new UsersClient(
            $httpHandler,
            new TokenFactory(),
            new UserFactory(),
            new ApiKeyFactory(),
        );

        self::$fileSourceClient = new FileSourceClient(new SourceFactory(), $httpHandler);
        self::$gitSourceClient = new GitSourceClient(new SourceFactory(), $httpHandler);

        self::$sourceClient = new SourceClient(new SourceFactory(), $httpHandler);
    }

    /**
     * @param callable(ApiKey $apiKey): ?object               $allowedAction
     * @param callable(ApiKey $apiKey, ?object $object): void $forbiddenAction
     */
    protected function doForbiddenActionTest(callable $allowedAction, callable $forbiddenAction): void
    {
        $user1RefreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user1ApiKey = self::$usersClient->getApiKey($user1RefreshableToken->token);

        $object = $allowedAction($user1ApiKey);

        $user2RefreshableToken = self::$usersClient->createToken(self::USER2_EMAIL, self::USER2_PASSWORD);
        $user2ApiKey = self::$usersClient->getApiKey($user2RefreshableToken->token);

        $exception = null;

        try {
            $forbiddenAction($user2ApiKey, $object);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }
}
