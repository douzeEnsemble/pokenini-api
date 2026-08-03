<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\TrainerDexLinkRepository;
use Doctrine\DBAL\Connection;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[CoversClass(TrainerDexLinkRepository::class)]
final class TrainerDexLinkRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    public function testInsertExistsAndGetOutgoingEdges(): void
    {
        $repository = self::getContainer()->get(TrainerDexLinkRepository::class);

        $sourceId = $this->getTrainerDexId('redgreenblueyellow');
        $targetId = $this->getTrainerDexId('goldsilvercrystal');

        $this->assertFalse($repository->exists($sourceId, $targetId));

        $repository->insert(self::TRAINER, $sourceId, $targetId, null);

        $this->assertTrue($repository->exists($sourceId, $targetId));

        $edges = $repository->getOutgoingEdges(self::TRAINER, $sourceId);

        $this->assertEquals(
            [['target_trainer_dex_id' => $targetId, 'target_dex_slug' => 'goldsilvercrystal']],
            $edges
        );
    }

    public function testGetOutgoingEdgesEmptyWhenNoLink(): void
    {
        $repository = self::getContainer()->get(TrainerDexLinkRepository::class);

        $sourceId = $this->getTrainerDexId('home');

        $this->assertSame([], $repository->getOutgoingEdges(self::TRAINER, $sourceId));
    }

    public function testGetForDexUnidirectional(): void
    {
        $repository = self::getContainer()->get(TrainerDexLinkRepository::class);

        $sourceId = $this->getTrainerDexId('redgreenblueyellow');
        $targetId = $this->getTrainerDexId('goldsilvercrystal');

        $repository->insert(self::TRAINER, $sourceId, $targetId, null);

        $fromSource = $repository->getForDex(self::TRAINER, 'redgreenblueyellow');
        $this->assertCount(1, $fromSource);
        $this->assertSame('to', $fromSource[0]['direction']);
        $this->assertSame('goldsilvercrystal', $fromSource[0]['target_dex_slug']);
        $this->assertNull($fromSource[0]['pair_id']);

        $fromTarget = $repository->getForDex(self::TRAINER, 'goldsilvercrystal');
        $this->assertCount(1, $fromTarget);
        $this->assertSame('from', $fromTarget[0]['direction']);
        $this->assertSame('redgreenblueyellow', $fromTarget[0]['target_dex_slug']);
    }

    public function testGetForDexBidirectionalIsMergedIntoOneRow(): void
    {
        $repository = self::getContainer()->get(TrainerDexLinkRepository::class);

        $sourceId = $this->getTrainerDexId('redgreenblueyellow');
        $targetId = $this->getTrainerDexId('goldsilvercrystal');
        $pairId = (string) Uuid::v4();

        $repository->insert(self::TRAINER, $sourceId, $targetId, $pairId);
        $repository->insert(self::TRAINER, $targetId, $sourceId, $pairId);

        $fromSource = $repository->getForDex(self::TRAINER, 'redgreenblueyellow');
        $this->assertCount(1, $fromSource);
        $this->assertSame('both', $fromSource[0]['direction']);
        $this->assertSame($pairId, $fromSource[0]['pair_id']);

        $fromTarget = $repository->getForDex(self::TRAINER, 'goldsilvercrystal');
        $this->assertCount(1, $fromTarget);
        $this->assertSame('both', $fromTarget[0]['direction']);
    }

    public function testDeleteByIdOrPairIdDeletesOnlyItselfWhenUnidirectional(): void
    {
        $repository = self::getContainer()->get(TrainerDexLinkRepository::class);

        $sourceId = $this->getTrainerDexId('redgreenblueyellow');
        $targetId = $this->getTrainerDexId('goldsilvercrystal');

        $repository->insert(self::TRAINER, $sourceId, $targetId, null);
        $id = $repository->getForDex(self::TRAINER, 'redgreenblueyellow')[0]['id'];

        $repository->deleteByIdOrPairId(self::TRAINER, $id);

        $this->assertFalse($repository->exists($sourceId, $targetId));
    }

    public function testDeleteByIdOrPairIdDeletesBothRowsWhenBidirectional(): void
    {
        $repository = self::getContainer()->get(TrainerDexLinkRepository::class);

        $sourceId = $this->getTrainerDexId('redgreenblueyellow');
        $targetId = $this->getTrainerDexId('goldsilvercrystal');
        $pairId = (string) Uuid::v4();

        $repository->insert(self::TRAINER, $sourceId, $targetId, $pairId);
        $repository->insert(self::TRAINER, $targetId, $sourceId, $pairId);
        $id = $repository->getForDex(self::TRAINER, 'redgreenblueyellow')[0]['id'];

        $repository->deleteByIdOrPairId(self::TRAINER, $id);

        $this->assertFalse($repository->exists($sourceId, $targetId));
        $this->assertFalse($repository->exists($targetId, $sourceId));
    }

    private function getTrainerDexId(string $dexSlug): string
    {
        $connection = self::getContainer()->get(Connection::class);

        /** @var string */
        return $connection->executeQuery(
            'SELECT id FROM trainer_dex WHERE slug = :slug AND trainer_external_id = :trainer',
            ['slug' => $dexSlug, 'trainer' => self::TRAINER]
        )->fetchOne();
    }
}
