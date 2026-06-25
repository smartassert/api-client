<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Results;

use webignition\BasilModels\Parser\Test\TestParser;

readonly class FactoryFactory
{
    public static function createEventFactory(): EventFactory
    {
        $resourceReferenceFactory = new ResourceReferenceFactory();

        return new EventFactory(
            $resourceReferenceFactory,
            new ResourceReferenceCollectionFactory($resourceReferenceFactory),
            TestParser::create(),
        );
    }
}
