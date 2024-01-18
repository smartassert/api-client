<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\FileSource;

use SmartAssert\ApiClient\Exception\Error\BadRequestException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceRequest\Error\BadRequestError;
use SmartAssert\ServiceRequest\Field\Field;
use SmartAssert\ServiceRequest\Field\Requirements;
use SmartAssert\ServiceRequest\Field\Size;

class CreateTest extends AbstractIntegrationTestCase
{
    public function testCreateUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$fileSourceClient->create(md5((string) rand()), md5((string) rand()));
    }

    /**
     * @dataProvider createBadRequestDataProvider
     */
    public function testCreateBadRequest(string $label, BadRequestError $expected): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $exception = null;

        try {
            self::$fileSourceClient->create($apiKey->key, $label);
        } catch (BadRequestException $exception) {
        }

        self::assertInstanceOf(BadRequestException::class, $exception);
        self::assertEquals($expected, $exception->error);
    }

    /**
     * @return array<mixed>
     */
    public static function createBadRequestDataProvider(): array
    {
        $labelEmpty = '';
        $labelTooLong = str_repeat('.', 256);

        return [
            'empty' => [
                'label' => $labelEmpty,
                'expected' => new BadRequestError(
                    (new Field('label', $labelEmpty))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'empty'
                ),
            ],
            'too long' => [
                'label' => $labelTooLong,
                'expected' => new BadRequestError(
                    (new Field('label', $labelTooLong))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'too_large'
                ),
            ],
        ];
    }

    public function testCreateFileSourceSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());

        $fileSource = self::$fileSourceClient->create($apiKey->key, $label);

        self::assertSame($label, $fileSource->label);
    }
}
