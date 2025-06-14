<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractTestControllerApi extends WebTestCase
{
    use RefreshDatabaseTrait;

    protected AbstractBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function assertResponseIsOK(): void
    {
        $this->assertEquals(
            200,
            $this->getResponse()->getStatusCode()
        );
    }

    public function assertResponseIsNotFound(): void
    {
        $this->assertEquals(
            404,
            $this->getResponse()->getStatusCode()
        );
    }

    public function getResponse(): Response
    {
        /** @var Response */
        return $this->client->getResponse();
    }

    public function getResponseContent(): string
    {
        return (string) $this->getResponse()->getContent();
    }

    /**
     * @return bool[]|int[]|string[]
     */
    public function getJsonDecodedResponseContent(): array
    {
        /** @var bool[]|int[]|string[] */
        return json_decode($this->getResponseContent(), true);
    }

    /**
     * @param string[]|string[][] $params
     * @param string[]|string[][] $options
     */
    public function apiRequest(
        string $method,
        string $route,
        array $params = [],
        ?array $options = null,
        ?string $content = null,
    ): void {
        $urlParams = \http_build_query($params);

        $this->client->request(
            $method,
            "/{$route}?{$urlParams}",
            [],
            [],
            array_merge(
                [
                    'headers' => [
                        'accept' => 'application/json',
                    ],
                ],
                $options ?? ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze']
            ),
            $content
        );
    }

    /**
     * @param string[]            $params
     * @param string[]|string[][] $options
     *
     * @return mixed[]
     */
    public function apiGetContent(
        string $route,
        array $params = [],
        array $options = ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze']
    ): array {
        $this->apiRequest('GET', $route, $params, $options);

        /** @var mixed[] */
        return json_decode((string) $this->getResponse()->getContent(), true);
    }
}
