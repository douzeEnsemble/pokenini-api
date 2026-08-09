<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\TrainerDexLinkController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(TrainerDexLinkController::class)]
final class TrainerDexLinkControllerTest extends AbstractTestControllerApi
{
    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        // A POST-then-GET (or POST-then-POST, or POST-then-GET-then-DELETE) within a
        // single test method must see the same uncommitted DB state. KernelBrowser
        // reboots the kernel (and thus the DB connection) between requests by
        // default, which would hide writes made by an earlier request in the same
        // test. Disabling the reboot keeps a single kernel/container/connection for
        // the whole test, so RefreshDatabaseTrait's per-test transaction (and its
        // rollback at teardown) stays intact. See ImagePipelineRunControllerTest for
        // the same pattern.
        $this->client->disableReboot();
    }

    #[Test]
    public function createListAndDelete(): void
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

    #[Test]
    public function createRejectsSelfLink(): void
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

    #[Test]
    public function createRejectsUnknownDex(): void
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

    #[Test]
    public function createRejectsDuplicateEdge(): void
    {
        $body = json_encode(
            ['sourceDexSlug' => 'redgreenblueyellow', 'targetDexSlug' => 'goldsilvercrystal', 'bidirectional' => false],
            JSON_THROW_ON_ERROR
        );

        $this->apiRequest('POST', '/trainer_dex_link/'.self::TRAINER, [], null, $body);
        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest('POST', '/trainer_dex_link/'.self::TRAINER, [], null, $body);
        $this->assertResponseStatusCodeSame(409);
    }

    #[Test]
    public function createEmptyBody(): void
    {
        $this->apiRequest('POST', '/trainer_dex_link/'.self::TRAINER, [], null, '');

        $this->assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function createMissingFields(): void
    {
        $this->apiRequest('POST', '/trainer_dex_link/'.self::TRAINER, [], null, json_encode(['sourceDexSlug' => 'redgreenblueyellow'], JSON_THROW_ON_ERROR));

        $this->assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function createRejectsNonBooleanBidirectional(): void
    {
        $this->apiRequest(
            'POST',
            '/trainer_dex_link/'.self::TRAINER,
            [],
            null,
            json_encode(
                ['sourceDexSlug' => 'redgreenblueyellow', 'targetDexSlug' => 'goldsilvercrystal', 'bidirectional' => 'yes'],
                JSON_THROW_ON_ERROR
            ),
        );

        $this->assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function createBidirectional(): void
    {
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

        /** @var array<int, array{direction: string}> $links */
        $links = $this->getJsonDecodedResponseContent();
        $this->assertCount(1, $links);
        $this->assertSame('both', $links[0]['direction']);
    }
}
