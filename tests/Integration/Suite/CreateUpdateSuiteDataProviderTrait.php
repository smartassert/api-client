<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Suite;

use SmartAssert\ServiceRequest\Error\BadRequestError;
use SmartAssert\ServiceRequest\Parameter\Parameter;
use SmartAssert\ServiceRequest\Parameter\Requirements;
use SmartAssert\ServiceRequest\Parameter\Size;

trait CreateUpdateSuiteDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createUpdateSuiteBadRequestDataProvider(): array
    {
        $labelTooLong = str_repeat('.', 256);

        return [
            'missing label' => [
                'label' => '',
                'tests' => [],
                'expected' => new BadRequestError(
                    (new Parameter('label', ''))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'wrong_size'
                ),
            ],
            'label length exceeds length limit' => [
                'label' => $labelTooLong,
                'tests' => [],
                'expected' => new BadRequestError(
                    (new Parameter('label', $labelTooLong))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'wrong_size'
                ),
            ],
            'invalid yaml filename within singular tests collection' => [
                'label' => md5((string) rand()),
                'tests' => ['test.txt'],
                'expected' => new BadRequestError(
                    (new Parameter('tests', ['test.txt']))
                        ->withRequirements(new Requirements('yaml_filename_collection'))
                        ->withErrorPosition(1),
                    'invalid'
                ),
            ],
            'invalid yaml filename within tests collection' => [
                'label' => md5((string) rand()),
                'tests' => ['test.yaml', 'test.txt', 'test.yml'],
                'expected' => new BadRequestError(
                    (new Parameter('tests', ['test.yaml', 'test.txt', 'test.yml']))
                        ->withRequirements(new Requirements('yaml_filename_collection'))
                        ->withErrorPosition(2),
                    'invalid'
                ),
            ],
        ];
    }
}
