<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

readonly class UrlGeneratorFactory
{
    public static function create(string $baseUrl): UrlGeneratorInterface
    {
        $routeCollection = new RouteCollection();
        $routeCollection->addCollection(self::createUserRoutes());
        $routeCollection->add('file-source', new Route('/source/file-source/{sourceId}', ['sourceId' => null]));
        $routeCollection->add('file-source-list', new Route('/source/file-source/{sourceId}/list/'));
        $routeCollection->add('git-source', new Route('/source/git-source/{sourceId}', ['sourceId' => null]));
        $routeCollection->add('file-source-file', new Route('/source/file-source/{sourceId}/{filename<.*>}'));
        $routeCollection->add('sources', new Route('/source/sources'));
        $routeCollection->add('source', new Route('/source/{sourceId}', ['sourceId' => null]));
        $routeCollection->add('suite', new Route('/source/suite/{suiteId}', ['suiteId' => null]));
        $routeCollection->add('suites', new Route('/source/suites'));
        $routeCollection->add('serialized-suite-create', new Route('/source/suite/{suiteId}/{serializedSuiteId}'));
        $routeCollection->add('serialized-suite-get', new Route('/source/serialized_suite/{serializedSuiteId}'));

        return new UrlGenerator($routeCollection, new RequestContext($baseUrl));
    }

    private static function createUserRoutes(): RouteCollection
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('user_token_create', new Route('/user/frontend-token/create'));
        $routeCollection->add('user_token_verify', new Route('/user/frontend-token/verify'));
        $routeCollection->add('user_token_refresh', new Route('/user/frontend-token/refresh'));
        $routeCollection->add('user_create', new Route('/user/create'));
        $routeCollection->add('user_refresh-token_revoke-all', new Route('/user/refresh-token/revoke-all-for-user'));
        $routeCollection->add('user_refresh-token_revoke', new Route('/user/refresh-token/revoke'));
        $routeCollection->add('user_apikey', new Route('/user/apikey'));
        $routeCollection->add('user_apikey_list', new Route('/user/apikey/list'));

        return $routeCollection;
    }
}
