<?php

namespace App\Tests\Functionnal\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractApiTest extends ApiTestCase
{
    /**
     * @param string[] $params
     */
    public function apiRequest(string $route, array $params = []): ResponseInterface
    {
        $urlParams = \http_build_query($params);

        return static::createClient()->request(
            'GET',
            "/{$route}?$urlParams",
            [
                'headers' => [
                    'accept' => 'application/json'
                ]
            ]
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
