<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

readonly class FactoryFactory
{
    public static function createJobFactory(): JobFactory
    {
        $metaStateFactory = new MetaStateFactory();

        return new JobFactory(
            new SummaryFactory(),
            $metaStateFactory,
            new ResultsJobFactory($metaStateFactory),
            new PreparationFactory($metaStateFactory),
            new SerializedSuiteFactory($metaStateFactory),
            new MachineFactory($metaStateFactory),
            self::createWorkerJobFactory(),
            new ServiceRequestFactory(),
        );
    }

    public static function createWorkerJobFactory(): WorkerJobFactory
    {
        return new WorkerJobFactory(
            new MetaStateFactory(),
            self::createWorkerJobCreationFailureFactory(),
        );
    }

    public static function createWorkerJobCreationFailureFactory(): WorkerJobCreationFailureFactory
    {
        return new WorkerJobCreationFailureFactory(new ExceptionFactory());
    }
}
