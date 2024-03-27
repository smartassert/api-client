<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\JobCoordinator;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ServiceRequest;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ServiceRequestAttempt;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJobComponent;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Factory\JobCoordinator\JobFactory;
use SmartAssert\ApiClient\JobCoordinatorClient;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceRequest\Error\BadRequestError;
use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;
use SmartAssert\ServiceRequest\Parameter\Parameter;
use SmartAssert\ServiceRequest\Parameter\Requirements;
use SmartAssert\ServiceRequest\Parameter\Size;
use Symfony\Component\Uid\Ulid;

class CreateTest extends AbstractIntegrationTestCase
{
    private JobCoordinatorClient $jobCoordinatorClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobCoordinatorClient = new JobCoordinatorClient(
            new JobFactory(),
            new HttpHandler(
                new HttpClient(),
                new ExceptionFactory(self::$errorDeserializer),
                new HttpFactory(),
                self::$urlGenerator,
            ),
        );
    }

    public function testCreateUnauthorized(): void
    {
        $exception = null;

        $suiteId = (string) new Ulid();
        \assert('' !== $suiteId);

        try {
            $this->jobCoordinatorClient->create(md5((string) rand()), $suiteId, rand(1, 1000));
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    /**
     * @dataProvider createJobBadRequestDataProvider
     */
    public function testCreateBadRequest(int $maximumDurationInSeconds, BadRequestErrorInterface $expected): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $suiteId = (string) new Ulid();
        \assert('' !== $suiteId);

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

    /**
     * @dataProvider createSuccessDataProvider
     */
    public function testCreateSuccess(int $maximumDurationInSeconds): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $suiteId = (string) new Ulid();
        \assert('' !== $suiteId);

        $job = $this->jobCoordinatorClient->create($apiKey->key, $suiteId, $maximumDurationInSeconds);

        self::assertSame($suiteId, $job->summary->suiteId);
        self::assertSame($maximumDurationInSeconds, $job->summary->maximumDurationInSeconds);

        self::assertSame('preparing', $job->preparation->state);
        self::assertSame(
            [
                'results_job' => 'requesting',
                'serialized_suite' => 'requesting',
                'machine' => 'pending',
                'worker_job' => 'pending',
            ],
            $job->preparation->requestStates,
        );

        self::assertNull($job->resultsJob->state);
        self::assertNull($job->resultsJob->endState);

        self::assertNull($job->serializedSuite->state);

        self::assertNull($job->machine->stateCategory);
        self::assertNull($job->machine->ipAddress);

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
                    'results/create',
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
