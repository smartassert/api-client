<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\GitSource;

use SmartAssert\ServiceRequest\Error\BadRequestError;
use SmartAssert\ServiceRequest\Field\Field;
use SmartAssert\ServiceRequest\Field\Requirements;
use SmartAssert\ServiceRequest\Field\Size;

trait CreateUpdateGitSourceDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function successDataProvider(): array
    {
        return [
            'without credentials' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => null,
                'expectedHasCredentials' => false,
            ],
            'with credentials' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => md5((string) rand()),
                'expectedHasCredentials' => true,
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function badRequestDataProvider(): array
    {
        $labelTooLong = str_repeat('.', 256);
        $hostUrlTooLong = str_repeat('.', 256);
        $pathTooLong = str_repeat('.', 256);
        $credentialsTooLong = str_repeat('.', 256);

        return [
            'label empty' => [
                'label' => '',
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => null,
                'expected' => new BadRequestError(
                    (new Field('label', ''))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'empty'
                ),
            ],
            'label too long' => [
                'label' => $labelTooLong,
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => null,
                'expected' => new BadRequestError(
                    (new Field('label', $labelTooLong))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'too_large'
                ),
            ],
            'host url empty' => [
                'label' => md5((string) rand()),
                'hostUrl' => '',
                'path' => md5((string) rand()),
                'credentials' => null,
                'expected' => new BadRequestError(
                    (new Field('host-url', ''))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'empty'
                ),
            ],
            'host url too long' => [
                'label' => md5((string) rand()),
                'hostUrl' => $hostUrlTooLong,
                'path' => md5((string) rand()),
                'credentials' => null,
                'expected' => new BadRequestError(
                    (new Field('host-url', $hostUrlTooLong))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'too_large'
                ),
            ],
            'path empty' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => '',
                'credentials' => null,
                'expected' => new BadRequestError(
                    (new Field('path', ''))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'empty'
                ),
            ],
            'path too long' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => $pathTooLong,
                'credentials' => null,
                'expected' => new BadRequestError(
                    (new Field('path', $pathTooLong))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'too_large'
                ),
            ],
            'credentials too long' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => $credentialsTooLong,
                'expected' => new BadRequestError(
                    (new Field('credentials', $credentialsTooLong))
                        ->withRequirements(new Requirements('string', new Size(0, 255))),
                    'too_large'
                ),
            ],
        ];
    }
}
