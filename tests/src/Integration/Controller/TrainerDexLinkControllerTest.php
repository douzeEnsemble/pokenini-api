<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\TrainerDexLinkController;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(TrainerDexLinkController::class)]
final class TrainerDexLinkControllerTest extends AbstractTestControllerApi
{
    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    public function testCreateListAndDelete(): void
    {
        $this->apiRequest('GET', '/trainer_dex_link/'.self::TRAINER.'/redgreenblueyellow');
        $this->assertResponseIsOK();
        $this->assertSame([], $this->getJsonDecodedResponseContent());

        $this->apiRequest(
            'POST',
            '/trainer_dex_link/'.self::TRAINER,
            [],
            null,
            json_encode(
                ['sourceDexSlug' => 'redgreenblueyellow', 'targetDexSlug' => 'goldsilvercrystal', 'bidirectional' => false],
                JSON_THROW_ON_ERROR
            ),
        );
        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest('GET', '/trainer_dex_link/'.self::TRAINER.'/redgreenblueyellow');
        $this->assertResponseIsOK();

        /** @var array<int, array{id: string, direction: string, target_dex_slug: string}> $links */
        $links = $this->getJsonDecodedResponseContent();
        $this->assertCount(1, $links);
        $this->assertSame('to', $links[0]['direction']);
        $this->assertSame('goldsilvercrystal', $links[0]['target_dex_slug']);

        $this->apiRequest('DELETE', '/trainer_dex_link/'.self::TRAINER.'/'.$links[0]['id']);
        $this->assertResponseIsOK();

        $this->apiRequest('GET', '/trainer_dex_link/'.self::TRAINER.'/redgreenblueyellow');
        $this->assertSame([], $this->getJsonDecodedResponseContent());
    }

    public function testCreateRejectsSelfLink(): void
    {
        $this->apiRequest(
            'POST',
            '/trainer_dex_link/'.self::TRAINER,
            [],
            null,
            json_encode(
                ['sourceDexSlug' => 'redgreenblueyellow', 'targetDexSlug' => 'redgreenblueyellow', 'bidirectional' => false],
                JSON_THROW_ON_ERROR
            ),
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateRejectsUnknownDex(): void
    {
        $this->apiRequest(
            'POST',
            '/trainer_dex_link/'.self::TRAINER,
            [],
            null,
            json_encode(
                ['sourceDexSlug' => 'redgreenblueyellow', 'targetDexSlug' => 'does-not-exist', 'bidirectional' => false],
                JSON_THROW_ON_ERROR
            ),
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateRejectsDuplicateEdge(): void
    {
        $this->apiRequest('GET', '/trainer_dex_link/'.self::TRAINER.'/redgreenblueyellow');

        $body = json_encode(
            ['sourceDexSlug' => 'redgreenblueyellow', 'targetDexSlug' => 'goldsilvercrystal', 'bidirectional' => false],
            JSON_THROW_ON_ERROR
        );

        $this->apiRequest('POST', '/trainer_dex_link/'.self::TRAINER, [], null, $body);
        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest('POST', '/trainer_dex_link/'.self::TRAINER, [], null, $body);
        $this->assertResponseStatusCodeSame(409);

        $this->apiRequest('GET', '/trainer_dex_link/'.self::TRAINER.'/redgreenblueyellow');

        /** @var array<int, array{id: string}> $links */
        $links = $this->getJsonDecodedResponseContent();
        $this->apiRequest('DELETE', '/trainer_dex_link/'.self::TRAINER.'/'.$links[0]['id']);
    }

    public function testCreateEmptyBody(): void
    {
        $this->apiRequest('POST', '/trainer_dex_link/'.self::TRAINER, [], null, '');

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateMissingFields(): void
    {
        $this->apiRequest('POST', '/trainer_dex_link/'.self::TRAINER, [], null, json_encode(['sourceDexSlug' => 'redgreenblueyellow'], JSON_THROW_ON_ERROR));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateBidirectional(): void
    {
        $this->apiRequest('GET', '/trainer_dex_link/'.self::TRAINER.'/goldsilvercrystal');

        $this->apiRequest(
            'POST',
            '/trainer_dex_link/'.self::TRAINER,
            [],
            null,
            json_encode(
                ['sourceDexSlug' => 'redgreenblueyellow', 'targetDexSlug' => 'goldsilvercrystal', 'bidirectional' => true],
                JSON_THROW_ON_ERROR
            ),
        );
        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest('GET', '/trainer_dex_link/'.self::TRAINER.'/goldsilvercrystal');

        /** @var array<int, array{id: string, direction: string}> $links */
        $links = $this->getJsonDecodedResponseContent();
        $this->assertCount(1, $links);
        $this->assertSame('both', $links[0]['direction']);

        $this->apiRequest('DELETE', '/trainer_dex_link/'.self::TRAINER.'/'.$links[0]['id']);
    }
}
