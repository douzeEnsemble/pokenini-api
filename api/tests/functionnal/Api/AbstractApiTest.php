<?php

namespace App\Tests\Functionnal\\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractApiTest extends ApiTestCase
{
    public function apiRequest(string $route): ResponseInterface
    {
        return static::createClient()->request(
            'GET',
            "/{$route}",
            [
                'headers' => [
                    'accept' => 'application/json'
                ]
            ]
        );
    }

    /**
     * @return mixed[]
     */
    public function apiRequestContent(string $route): array
    {
        $response = $this->apiRequest($route);

        /** @var mixed[] */
        return json_decode($response->getContent(), true);
    }
}
