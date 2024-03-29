<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\GitSource;

use SmartAssert\ServiceRequest\Error\BadRequestError;
use SmartAssert\ServiceRequest\Parameter\Parameter;
use SmartAssert\ServiceRequest\Parameter\Requirements;
use SmartAssert\ServiceRequest\Parameter\Size;

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
                    (new Parameter('label', ''))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'wrong_size'
                ),
            ],
            'label too long' => [
                'label' => $labelTooLong,
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => null,
                'expected' => new BadRequestError(
                    (new Parameter('label', $labelTooLong))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'wrong_size'
                ),
            ],
            'host url empty' => [
                'label' => md5((string) rand()),
                'hostUrl' => '',
                'path' => md5((string) rand()),
                'credentials' => null,
                'expected' => new BadRequestError(
                    (new Parameter('host-url', ''))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'wrong_size'
                ),
            ],
            'host url too long' => [
                'label' => md5((string) rand()),
                'hostUrl' => $hostUrlTooLong,
                'path' => md5((string) rand()),
                'credentials' => null,
                'expected' => new BadRequestError(
                    (new Parameter('host-url', $hostUrlTooLong))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'wrong_size'
                ),
            ],
            'path empty' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => '',
                'credentials' => null,
                'expected' => new BadRequestError(
                    (new Parameter('path', ''))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'wrong_size'
                ),
            ],
            'path too long' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => $pathTooLong,
                'credentials' => null,
                'expected' => new BadRequestError(
                    (new Parameter('path', $pathTooLong))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'wrong_size'
                ),
            ],
            'credentials too long' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => $credentialsTooLong,
                'expected' => new BadRequestError(
                    (new Parameter('credentials', $credentialsTooLong))
                        ->withRequirements(new Requirements('string', new Size(0, 255))),
                    'wrong_size'
                ),
            ],
        ];
    }
}
