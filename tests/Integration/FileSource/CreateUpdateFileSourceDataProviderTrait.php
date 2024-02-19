<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\FileSource;

use SmartAssert\ServiceRequest\Error\BadRequestError;
use SmartAssert\ServiceRequest\Parameter\Parameter;
use SmartAssert\ServiceRequest\Parameter\Requirements;
use SmartAssert\ServiceRequest\Parameter\Size;

trait CreateUpdateFileSourceDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createUpdateFileSourceBadRequestDataProvider(): array
    {
        $labelEmpty = '';
        $labelTooLong = str_repeat('.', 256);

        return [
            'empty' => [
                'label' => $labelEmpty,
                'expected' => new BadRequestError(
                    (new Parameter('label', $labelEmpty))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'wrong_size'
                ),
            ],
            'too long' => [
                'label' => $labelTooLong,
                'expected' => new BadRequestError(
                    (new Parameter('label', $labelTooLong))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'wrong_size'
                ),
            ],
        ];
    }
}
