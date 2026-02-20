<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\JobCoordinator;

use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ServiceRequest;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ServiceRequestAttempt;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJobComponent;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ServiceRequest\Error\BadRequestError;
use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;
use SmartAssert\ServiceRequest\Parameter\Parameter;
use SmartAssert\ServiceRequest\Parameter\Requirements;
use SmartAssert\ServiceRequest\Parameter\Size;
use Symfony\Component\Uid\Ulid;

class CreateTest extends AbstractJobCoordinatorClientTestCase
{
    public function testCreateUnauthorized(): void
    {
        $exception = null;
        $suiteId = (string) new Ulid();

        try {
            $this->jobCoordinatorClient->create(md5((string) rand()), $suiteId, rand(1, 1000));
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    #[DataProvider('createJobBadRequestDataProvider')]
    public function testCreateBadRequest(int $maximumDurationInSeconds, BadRequestErrorInterface $expected): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $suiteId = (string) new Ulid();
        $exception = null;

        try {
            $this->jobCoordinatorClient->create($apiKey->key, $suiteId, $maximumDurationInSeconds);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $errorException = $exception->getInnerException();
        self::assertInstanceOf(ErrorException::class, $errorException);

        $error = $errorException->getError();
        self::assertInstanceOf(BadRequestErrorInterface::class, $error);
        self::assertEquals($expected, $error);
    }

    /**
     * @return array<mixed>
     */
    public static function createJobBadRequestDataProvider(): array
    {
        return [
            'maximum_duration_in_seconds too small' => [
                'maximumDurationInSeconds' => 0,
                'expected' => new BadRequestError(
                    (new Parameter('maximum_duration_in_seconds', 0))
                        ->withRequirements(new Requirements('integer', new Size(1, 2147483647))),
                    'wrong_size'
                ),
            ],
            'maximum_duration_in_seconds too large' => [
                'maximumDurationInSeconds' => 2147483648,
                'expected' => new BadRequestError(
                    (new Parameter('maximum_duration_in_seconds', 2147483648))
                        ->withRequirements(new Requirements('integer', new Size(1, 2147483647))),
                    'wrong_size'
                ),
            ],
        ];
    }

    #[DataProvider('createSuccessDataProvider')]
    public function testCreateSuccess(int $maximumDurationInSeconds): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $suiteId = (string) new Ulid();

        $job = $this->jobCoordinatorClient->create($apiKey->key, $suiteId, $maximumDurationInSeconds);

        self::assertSame($suiteId, $job->summary->suiteId);
        self::assertSame($maximumDurationInSeconds, $job->summary->maximumDurationInSeconds);

        self::assertSame('preparing', $job->preparation->state);
        self::assertEquals(
            [
                'results-job' => 'requesting',
                'serialized-suite' => 'requesting',
                'machine' => 'pending',
                'worker-job' => 'pending',
            ],
            $job->preparation->requestStates,
        );

        self::assertNull($job->resultsJob);
        self::assertNull($job->serializedSuite);
        self::assertNull($job->machine);

        self::assertSame('pending', $job->workerJob->state);
        self::assertFalse($job->workerJob->isEndState);
        self::assertEquals(
            [
                'compilation' => new WorkerJobComponent('pending', false),
                'execution' => new WorkerJobComponent('pending', false),
                'event_delivery' => new WorkerJobComponent('pending', false),
            ],
            $job->workerJob->componentStates,
        );

        self::assertEquals(
            [
                new ServiceRequest(
                    'results-job/create',
                    [
                        new ServiceRequestAttempt('requesting'),
                    ]
                ),
                new ServiceRequest(
                    'serialized-suite/create',
                    [
                        new ServiceRequestAttempt('requesting'),
                    ]
                ),
            ],
            $job->serviceRequests
        );
    }

    /**
     * @return array<mixed>
     */
    public static function createSuccessDataProvider(): array
    {
        return [
            'maximum duration 600' => [
                'maximumDurationInSeconds' => 600,
            ],
        ];
    }
}
