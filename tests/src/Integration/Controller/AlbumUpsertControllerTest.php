<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\AlbumUpsertController;
use App\DTO\Response\AlbumUpsertResponse;
use App\Tests\Common\Traits\CounterTrait\CountTrainerDexTrait;
use App\Tests\Common\Traits\GetterTrait\GetPokedexTrait;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(AlbumUpsertController::class)]
#[CoversClass(AlbumUpsertResponse::class)]
final class AlbumUpsertControllerTest extends AbstractTestControllerApi
{
    use GetPokedexTrait;
    use CountTrainerDexTrait;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        // testUpdateCascadesThroughAnActiveLink() makes two requests (POST /trainer_dex_link,
        // then PATCH /album/...) that must see the same uncommitted DB state. KernelBrowser
        // reboots the kernel (and thus the DB connection) between requests by default, which
        // would hide the link created by the first request. Disabling the reboot keeps a
        // single kernel/container/connection for the whole test, so RefreshDatabaseTrait's
        // per-test transaction (and its rollback at teardown) stays intact. See
        // TrainerDexLinkControllerTest for the same pattern.
        $this->client->disableReboot();
    }

    #[Test]
    public function update(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('redgreenblueyellow', 'ivysaur');

        $this->assertArrayHasKey('slug', $pokedexBefore);
        $this->assertEquals('maybe', $pokedexBefore['slug']);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $this->apiRequest(
            'PATCH',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'yes'
        );

        $this->assertResponseIsSuccessful();
        $this->assertEquals(
            ['updatedDexSlugs' => ['redgreenblueyellow']],
            $this->getJsonDecodedResponseContent()
        );

        $this->assertEquals(34, $this->getTrainerDexCount());
    }

    #[Test]
    public function updateEmpty(): void
    {
        $this->apiRequest(
            'PATCH',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            ''
        );

        $this->assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function updateNonExistingDex(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('douze', 'ivysaur');

        $this->assertEmpty($pokedexBefore);

        $this->apiRequest(
            'PATCH',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/douze/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'yes'
        );

        $this->assertResponseIsSuccessful();
        $this->assertEquals(
            ['updatedDexSlugs' => ['douze']],
            $this->getJsonDecodedResponseContent()
        );

        $pokedexAfter = $this->getPokedexFromSlugs('douze', 'ivysaur');

        $this->assertEmpty($pokedexAfter);
    }

    #[Test]
    public function updateNonExistingPokemon(): void
    {
        $this->apiRequest(
            'PATCH',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/treize',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'yes'
        );

        $this->assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function create(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('goldsilvercrystal', 'douze');

        $this->assertEmpty($pokedexBefore);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $this->apiRequest(
            'PUT',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/goldsilvercrystal/douze',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'maybenot'
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
        $this->assertEquals(
            ['updatedDexSlugs' => ['goldsilvercrystal']],
            $this->getJsonDecodedResponseContent()
        );

        $pokedexAfter = $this->getPokedexFromSlugs('goldsilvercrystal', 'douze');

        $this->assertArrayHasKey('slug', $pokedexAfter);
        $this->assertEquals('maybenot', $pokedexAfter['slug']);

        $this->assertEquals(34, $this->getTrainerDexCount());
    }

    #[Test]
    public function createNonExistingTrainerDex(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('spoon', 'douze');

        $this->assertEmpty($pokedexBefore);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $this->apiRequest(
            'PUT',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/spoon/douze',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'maybenot'
        );

        $this->assertResponseIsSuccessful();
        $this->assertEquals(
            ['updatedDexSlugs' => ['spoon']],
            $this->getJsonDecodedResponseContent()
        );

        $pokedexAfter = $this->getPokedexFromSlugs('spoon', 'douze');

        $this->assertArrayHasKey('slug', $pokedexAfter);
        $this->assertEquals('maybenot', $pokedexAfter['slug']);

        $this->assertEquals(35, $this->getTrainerDexCount());
    }

    #[Test]
    public function createNonExistingDex(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('douze', 'ivysaur');

        $this->assertEmpty($pokedexBefore);

        $this->apiRequest(
            'PUT',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/douze/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'yes'
        );

        $this->assertResponseIsSuccessful();
        $this->assertEquals(
            ['updatedDexSlugs' => ['douze']],
            $this->getJsonDecodedResponseContent()
        );

        $pokedexAfter = $this->getPokedexFromSlugs('douze', 'ivysaur');

        $this->assertEmpty($pokedexAfter);
    }

    #[Test]
    public function createNonExistingPokemon(): void
    {
        $this->apiRequest(
            'PUT',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/treize',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'yes'
        );

        $this->assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function createEmpty(): void
    {
        $this->apiRequest(
            'PUT',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            ''
        );

        $this->assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function updateCascadesThroughAnActiveLink(): void
    {
        $this->apiRequest(
            'POST',
            '/trainer_dex_link/7b52009b64fd0a2a49e6d8a939753077792b0554',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            json_encode(
                ['sourceDexSlug' => 'redgreenblueyellow', 'targetDexSlug' => 'goldsilvercrystal', 'bidirectional' => false],
                JSON_THROW_ON_ERROR
            ),
        );
        $this->assertResponseStatusCodeSame(201);

        // Fixture: ivysaur is 'no' in goldsilvercrystal, 'maybe' in redgreenblueyellow, for this trainer.
        // NB: several other trainer fixtures also own a "goldsilvercrystal" dex, so we look up the
        // pokedex row scoped to *this* trainer explicitly instead of using GetPokedexTrait's
        // unscoped-by-trainer helper, which could otherwise match a different trainer's row that
        // happens to share the same dex slug and silently hide a broken cascade.
        $pokedexBefore = $this->getPokedexFromSlugsForTrainer(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'goldsilvercrystal',
            'ivysaur'
        );
        $this->assertEquals('no', $pokedexBefore['slug']);

        $this->apiRequest(
            'PATCH',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'yes'
        );

        $this->assertResponseIsSuccessful();
        $this->assertEquals(
            ['updatedDexSlugs' => ['redgreenblueyellow', 'goldsilvercrystal']],
            $this->getJsonDecodedResponseContent()
        );

        $pokedexAfter = $this->getPokedexFromSlugsForTrainer(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'goldsilvercrystal',
            'ivysaur'
        );
        $this->assertEquals('yes', $pokedexAfter['slug']);
    }

    /**
     * @return array<string, mixed>
     */
    private function getPokedexFromSlugsForTrainer(string $trainerExternalId, string $dexSlug, string $pokemonSlug): array
    {
        $connection = self::getContainer()->get(Connection::class);

        $sql = <<<'SQL'
            SELECT      cs.*
            FROM        pokedex AS pd
                JOIN pokemon AS p
                    ON pd.pokemon_id = p.id AND p.slug = :pokemon_slug
                JOIN trainer_dex AS td
                    ON pd.trainer_dex_id = td.id
                JOIN catch_state AS cs
                    ON pd.catch_state_id = cs.id
            WHERE   td.slug = :dex_slug
                AND td.trainer_external_id = :trainer_external_id
            SQL;

        $result = $connection->executeQuery($sql, [
            'trainer_external_id' => $trainerExternalId,
            'dex_slug' => $dexSlug,
            'pokemon_slug' => $pokemonSlug,
        ])->fetchAssociative();

        $this->assertIsArray($result, 'Expected a pokedex row for this trainer/dex/pokemon combination');

        return $result;
    }
}
