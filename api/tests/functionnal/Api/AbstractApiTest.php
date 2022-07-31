<?php

namespace App\Tests\Functionnal\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
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
        array $options = []
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
     *
     * @return mixed[]
     */
    public function apiRequestContent(string $route, array $params = []): array
    {
        $response = $this->apiRequest($route, $params);

        /** @var mixed[] */
        return json_decode($response->getContent(), true);
    }
}
