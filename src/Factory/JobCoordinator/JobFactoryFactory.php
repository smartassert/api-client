<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

readonly class JobFactoryFactory
{
    public function create(): JobFactory
    {
        $metaStateFactory = new MetaStateFactory();

        return new JobFactory(
            new SummaryFactory(),
            $metaStateFactory,
            new ResultsJobFactory($metaStateFactory),
            new PreparationFactory($metaStateFactory),
            new SerializedSuiteFactory($metaStateFactory),
            new MachineFactory($metaStateFactory),
            new WorkerJobFactory($metaStateFactory),
            new ServiceRequestFactory(),
        );
    }
}
