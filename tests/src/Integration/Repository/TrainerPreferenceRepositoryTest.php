<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\TrainerPreferenceRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(TrainerPreferenceRepository::class)]
final class TrainerPreferenceRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    #[Test]
    public function getHomePageWithoutPreference(): void
    {
        $repository = self::getContainer()->get(TrainerPreferenceRepository::class);

        $this->assertNull($repository->getHomePage(self::TRAINER));
    }

    #[Test]
    public function upsertHomePageInsertsThenUpdates(): void
    {
        $repository = self::getContainer()->get(TrainerPreferenceRepository::class);

        $repository->upsertHomePage(self::TRAINER, 'album');

        $this->assertSame('album', $repository->getHomePage(self::TRAINER));
        $this->assertNull($repository->getHomePage('another-trainer'));

        $repository->upsertHomePage(self::TRAINER, 'game');

        $this->assertSame('game', $repository->getHomePage(self::TRAINER));
        $this->assertSame(1, $repository->count(['trainerExternalId' => self::TRAINER]));
    }
}
