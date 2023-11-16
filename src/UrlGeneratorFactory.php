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
        $routeCollection->addCollection(self::createFileSourceRoutes());
        $routeCollection->addCollection(self::createGitSourceRoutes());

        return new UrlGenerator($routeCollection, new RequestContext($baseUrl));
    }

    private static function createUserRoutes(): RouteCollection
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('user_token_create', new Route('/user/token/create'));
        $routeCollection->add('user_token_verify', new Route('/user/token/verify'));
        $routeCollection->add('user_token_refresh', new Route('/user/token/refresh'));
        $routeCollection->add('user_create', new Route('/user/create'));
        $routeCollection->add('user_refresh-token_revoke-all', new Route('/user/refresh_token/revoke-all'));
        $routeCollection->add('user_refresh-token_revoke', new Route('/user/refresh_token/revoke'));
        $routeCollection->add('user_apikey', new Route('/user/apikey/'));
        $routeCollection->add('user_apikey_list', new Route('/user/apikey/list'));

        return $routeCollection;
    }

    private static function createFileSourceRoutes(): RouteCollection
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('file-source_create', new Route('/file-source'));
        $routeCollection->add('file-source', new Route('/file-source/{sourceId}'));

        return $routeCollection;
    }

    private static function createGitSourceRoutes(): RouteCollection
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('git-source_create', new Route('/git-source'));
        $routeCollection->add('git-source', new Route('/git-source/{sourceId}'));

        return $routeCollection;
    }
}
