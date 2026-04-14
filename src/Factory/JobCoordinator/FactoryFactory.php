<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

readonly class FactoryFactory
{
    public static function createJobFactory(): JobFactory
    {
        $metaStateFactory = new MetaStateFactory();
        $serviceRequestFactory = new ServiceRequestFactory();
        $componentPreparationFactory = new ComponentPreparationFactory(
            new ComponentPreparationFailureFactory()
        );

        return new JobFactory(
            new SummaryFactory(),
            $metaStateFactory,
            new ResultsJobFactory($metaStateFactory, $serviceRequestFactory, $componentPreparationFactory),
            new PreparationFactory($metaStateFactory),
            new SerializedSuiteFactory($metaStateFactory, $serviceRequestFactory, $componentPreparationFactory),
            new MachineFactory($metaStateFactory, $serviceRequestFactory, $componentPreparationFactory),
            new WorkerJobFactory(
                $metaStateFactory,
                $serviceRequestFactory,
                $componentPreparationFactory,
                self::createWorkerJobCreationFailureFactory(),
            )
        );
    }

    public static function createWorkerJobCreationFailureFactory(): WorkerJobCreationFailureFactory
    {
        return new WorkerJobCreationFailureFactory(new ExceptionFactory());
    }
}
