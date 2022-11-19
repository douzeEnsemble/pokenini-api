<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractApiTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    /**
     * @param string[] $params
     * @param string[]|string[][] $options
     */
    public function apiRequest(
        string $route,
        array $params = [],
        string $method = 'GET',
        array $options = ['auth_basic' => ['web', 'douze']]
    ): ResponseInterface {
        $urlParams = \http_build_query($params);

        return static::createClient()->request(
            $method,
            "/{$route}?$urlParams",
            array_merge(
                [
                    'headers' => [
                        'accept' => 'application/json'
                    ]
                ],
                $options
            )
        );
    }

    /**
     * @param string[] $params
     * @param string[]|string[][] $options
     *
     * @return mixed[]
     */
    public function apiRequestContent(
        string $route,
        array $params = [],
        string $method = 'GET',
        array $options = ['auth_basic' => ['web', 'douze']]
    ): array {
        $response = $this->apiRequest($route, $params, $method, $options);

        /** @var mixed[] */
        return json_decode($response->getContent(), true);
    }
}
