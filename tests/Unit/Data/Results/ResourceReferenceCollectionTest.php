<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Unit\Data\Results;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\ApiClient\Data\Results\ResourceReference;
use SmartAssert\ApiClient\Data\Results\ResourceReferenceCollection;

class ResourceReferenceCollectionTest extends TestCase
{
    #[DataProvider('getForLabelDataProvider')]
    public function testGetForLabel(
        ResourceReferenceCollection $collection,
        string $label,
        ?ResourceReference $expected
    ): void {
        self::assertEquals($expected, $collection->getForLabel($label));
    }

    /**
     * @return array<mixed>
     */
    public static function getForLabelDataProvider(): array
    {
        $labels = [
            uniqid('label_', true),
            uniqid('label_', true),
            uniqid('label_', true),
        ];

        $references = [
            new ResourceReference($labels[0], 'label 1 reference'),
            new ResourceReference($labels[1], 'label 2 reference'),
            new ResourceReference($labels[2], 'label 3 reference'),
        ];

        $collection = new ResourceReferenceCollection($references);

        return [
            'empty' => [
                'collection' => new ResourceReferenceCollection(),
                'label' => 'label',
                'expected' => null,
            ],
            'not found' => [
                'collection' => $collection,
                'label' => 'not-found',
                'expected' => null,
            ],
            'found (1)' => [
                'collection' => $collection,
                'label' => $labels[0],
                'expected' => $references[0],
            ],
            'found (2)' => [
                'collection' => $collection,
                'label' => $labels[1],
                'expected' => $references[1],
            ],
            'found (3)' => [
                'collection' => $collection,
                'label' => $labels[2],
                'expected' => $references[2],
            ],
        ];
    }
}
