<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\FileSourceClient;

use SmartAssert\ApiClient\FileSourceClient;
use SmartAssert\ApiClient\Model\Source\FileSource;
use SmartAssert\ApiClient\Tests\Functional\Client\ClientActionThrowsInvalidModelDataExceptionTestTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;

abstract class AbstractFileSourceTestCase extends AbstractFileSourceClientTestCase
{
    use CommonNonSuccessResponseDataProviderTrait;
    use ClientActionThrowsInvalidModelDataExceptionTestTrait;

    protected const ID = 'id';
    protected const LABEL = 'label';

    protected FileSourceClient $client;

    protected function getExpectedModelClass(): string
    {
        return FileSource::class;
    }
}
