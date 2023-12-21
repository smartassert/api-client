<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\FileSource;

use GuzzleHttp\Client as HttpClient;
use SmartAssert\ApiClient\FileClient;
use SmartAssert\ApiClient\FooException\Http\UnauthorizedException;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use Symfony\Component\Uid\Ulid;

class ListTest extends AbstractIntegrationTestCase
{
    public function testListUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);
        $id = (string) new Ulid();
        \assert('' !== $id);

        self::expectException(UnauthorizedException::class);

        self::$fileSourceClient->list(md5((string) rand()), $id);
    }

    /**
     * @dataProvider listSuccessDataProvider
     *
     * @param non-empty-string[] $filenamesToCreate
     * @param string[]           $expected
     */
    public function testListSuccess(array $filenamesToCreate, array $expected): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());

        $fileSource = self::$fileSourceClient->create($apiKey->key, $label);

        $fileClient = new FileClient(self::$fooUrlGenerator, new HttpHandler(new HttpClient()));

        foreach ($filenamesToCreate as $filename) {
            $fileClient->create($apiKey->key, $fileSource->id, $filename, md5((string) rand()));
        }

        $list = self::$fileSourceClient->list($apiKey->key, $fileSource->id);

        self::assertSame($expected, $list);
    }

    /**
     * @return array<mixed>
     */
    public static function listSuccessDataProvider(): array
    {
        return [
            'empty' => [
                'filenamesToCreate' => [],
                'expected' => [],
            ],
            'single' => [
                'filenamesToCreate' => [
                    'A.yaml'
                ],
                'expected' => ['A.yaml'],
            ],
            'multiple' => [
                'filenamesToCreate' => [
                    'A.yaml',
                    'Z.yaml',
                    'M.yaml',
                ],
                'expected' => [
                    'A.yaml',
                    'M.yaml',
                    'Z.yaml',
                ],
            ],
        ];
    }
}
