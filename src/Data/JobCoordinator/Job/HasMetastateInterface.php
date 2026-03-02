<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

interface HasMetastateInterface
{
    public function getMetaState(): MetaState;
}
