<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\FileSource;

use SmartAssert\ServiceRequest\Error\BadRequestError;
use SmartAssert\ServiceRequest\Field\Field;
use SmartAssert\ServiceRequest\Field\Requirements;
use SmartAssert\ServiceRequest\Field\Size;

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
}
