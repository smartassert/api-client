<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\FileSource;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\ApiClient\Data\Source\File;
use SmartAssert\ApiClient\Data\Source\FileSource;
use SmartAssert\ApiClient\Data\User\ApiKey;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\FileClient;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use Symfony\Component\Uid\Ulid;

class ListTest extends AbstractIntegrationTestCase
{
    public function testListUnauthorized(): void
    {
        $id = (string) new Ulid();
        \assert('' !== $id);

        $exception = null;

        try {
            self::$fileSourceClient->list(md5((string) rand()), $id);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    /**
     * @param array<array{path: non-empty-string, content: string}> $fileDataCollection
     * @param string[]                                              $expected
     */
    #[DataProvider('listSuccessDataProvider')]
    public function testListSuccess(array $fileDataCollection, array $expected): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());

        $fileSource = self::$fileSourceClient->create($apiKey->key, $label);

        $fileClient = new FileClient(
            new HttpHandler(
                new HttpClient(),
                new ExceptionFactory(self::$errorDeserializer),
                new HttpFactory(),
                self::$urlGenerator,
            ),
        );

        foreach ($fileDataCollection as $fileData) {
            $fileClient->create($apiKey->key, $fileSource->id, $fileData['path'], $fileData['content']);
        }

        $list = self::$fileSourceClient->list($apiKey->key, $fileSource->id);

        self::assertEquals($expected, $list);
    }

    /**
     * @return array<mixed>
     */
    public static function listSuccessDataProvider(): array
    {
        return [
            'empty' => [
                'fileDataCollection' => [],
                'expected' => [],
            ],
            'single file, size 1' => [
                'fileDataCollection' => [
                    [
                        'path' => 'A.yaml',
                        'content' => '.',
                    ],
                ],
                'expected' => [
                    new File('A.yaml', 1),
                ],
            ],
            'multiple' => [
                'fileDataCollection' => [
                    [
                        'path' => 'A.yaml',
                        'content' => 'a',
                    ],
                    [
                        'path' => 'Z.yaml',
                        'content' => 'zzzzzzzzzzzzzzzzzzzzzzzzzz',
                    ],
                    [
                        'path' => 'M.yaml',
                        'content' => 'mmmmmmmmmmmmm',
                    ],
                ],
                'expected' => [
                    new File('A.yaml', 1),
                    new File('M.yaml', 13),
                    new File('Z.yaml', 26),
                ],
            ],
        ];
    }

    public function testListForbidden(): void
    {
        $this->doForbiddenActionTest(
            function (ApiKey $apiKey) {
                return self::$fileSourceClient->create($apiKey->key, md5((string) rand()));
            },
            function (ApiKey $apiKey, ?object $source) {
                if (!$source instanceof FileSource) {
                    return;
                }

                self::$fileSourceClient->list($apiKey->key, $source->id);
            },
        );
    }

    public function testListNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $id = (string) new Ulid();
        \assert('' !== $id);

        $exception = null;

        try {
            self::$fileSourceClient->list($apiKey->key, $id);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }
}
