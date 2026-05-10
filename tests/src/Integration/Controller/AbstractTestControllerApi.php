<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractTestControllerApi extends WebTestCase
{
    use RefreshDatabaseTrait;

    protected KernelBrowser $client;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function assertResponseIsOK(): void
    {
        $this->assertEquals(
            200,
            $this->getClientResponse()->getStatusCode()
        );
    }

    public function assertJsonResponseIsOK(): void
    {
        $this->assertResponseIsOK();
        $this->assertEquals(
            'application/json',
            $this->getClientResponse()->headers->get('Content-Type')
        );
    }

    public function assertResponseIsNotFound(): void
    {
        $this->assertEquals(
            404,
            $this->getClientResponse()->getStatusCode()
        );
    }

    public function getClientResponse(): Response
    {
        /** @var Response */
        return $this->client->getResponse();
    }

    public function getClientResponseContent(): string
    {
        return (string) $this->getClientResponse()->getContent();
    }

    /**
     * @return bool[]|int[]|string[]
     */
    public function getJsonDecodedResponseContent(): array
    {
        /** @var bool[]|int[]|string[] */
        return json_decode($this->getClientResponseContent(), true);
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
            "{$route}?{$urlParams}",
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
}
