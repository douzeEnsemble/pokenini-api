<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\TrainerDex;
use App\Exception\DuplicateTrainerDexLinkException;
use App\Exception\SelfTrainerDexLinkException;
use App\Exception\TrainerDexNotFoundException;
use App\Repository\TrainerDexLinkRepository;
use App\Repository\TrainerDexRepository;
use App\Service\TrainerDexLinkService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[CoversClass(TrainerDexLinkService::class)]
final class TrainerDexLinkServiceTest extends TestCase
{
    #[Test]
    public function listForDexDelegatesToRepository(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->once())
            ->method('getForDex')
            ->with('trainer-1', 'national')
            ->willReturn([['id' => 'link-1']])
        ;

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $this->assertSame([['id' => 'link-1']], $service->listForDex('trainer-1', 'national'));
    }

    #[Test]
    public function createRejectsSelfLink(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->never())->method('exists');
        $linkRepository->expects($this->never())->method('insert');

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);
        $trainerDexRepository->expects($this->never())->method('findOneBy');

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $this->expectException(SelfTrainerDexLinkException::class);

        $service->create('trainer-1', 'national', 'national', false);
    }

    #[Test]
    public function createRejectsUnknownSourceDex(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);
        $trainerDexRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['trainerExternalId' => 'trainer-1', 'slug' => 'unknown'])
            ->willReturn(null)
        ;

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $this->expectException(TrainerDexNotFoundException::class);

        $service->create('trainer-1', 'unknown', 'shiny', false);
    }

    #[Test]
    public function createRejectsUnknownTargetDex(): void
    {
        $source = new TrainerDex();

        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);
        $trainerDexRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnMap([
                [['trainerExternalId' => 'trainer-1', 'slug' => 'national'], null, $source],
                [['trainerExternalId' => 'trainer-1', 'slug' => 'unknown'], null, null],
            ])
        ;

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $this->expectException(TrainerDexNotFoundException::class);

        $service->create('trainer-1', 'national', 'unknown', false);
    }

    #[Test]
    public function createRejectsDuplicateEdge(): void
    {
        $source = $this->trainerDexWithId('11111111-1111-1111-1111-111111111111');
        $target = $this->trainerDexWithId('22222222-2222-2222-2222-222222222222');

        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->once())
            ->method('exists')
            ->with('11111111-1111-1111-1111-111111111111', '22222222-2222-2222-2222-222222222222')
            ->willReturn(true)
        ;
        $linkRepository->expects($this->never())->method('insert');

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);
        $trainerDexRepository->method('findOneBy')->willReturnOnConsecutiveCalls($source, $target);

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $this->expectException(DuplicateTrainerDexLinkException::class);

        $service->create('trainer-1', 'national', 'shiny', false);
    }

    #[Test]
    public function createInsertsOneRowForAUnidirectionalLink(): void
    {
        $source = $this->trainerDexWithId('11111111-1111-1111-1111-111111111111');
        $target = $this->trainerDexWithId('22222222-2222-2222-2222-222222222222');

        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->method('exists')->willReturn(false);
        $linkRepository->expects($this->once())
            ->method('insert')
            ->with(
                'trainer-1',
                '11111111-1111-1111-1111-111111111111',
                '22222222-2222-2222-2222-222222222222',
                null,
            )
        ;

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);
        $trainerDexRepository->method('findOneBy')->willReturnOnConsecutiveCalls($source, $target);

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $service->create('trainer-1', 'national', 'shiny', false);
    }

    #[Test]
    public function createRejectsWhenTheReverseEdgeOfABidirectionalLinkAlreadyExists(): void
    {
        $source = $this->trainerDexWithId('11111111-1111-1111-1111-111111111111');
        $target = $this->trainerDexWithId('22222222-2222-2222-2222-222222222222');

        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->method('exists')->willReturnMap([
            ['11111111-1111-1111-1111-111111111111', '22222222-2222-2222-2222-222222222222', false],
            ['22222222-2222-2222-2222-222222222222', '11111111-1111-1111-1111-111111111111', true],
        ]);
        $linkRepository->expects($this->never())->method('insert');

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);
        $trainerDexRepository->method('findOneBy')->willReturnOnConsecutiveCalls($source, $target);

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $this->expectException(DuplicateTrainerDexLinkException::class);

        $service->create('trainer-1', 'national', 'shiny', true);
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    #[Test]
    public function createInsertsTwoRowsSharingAPairIdForABidirectionalLink(): void
    {
        $source = $this->trainerDexWithId('11111111-1111-1111-1111-111111111111');
        $target = $this->trainerDexWithId('22222222-2222-2222-2222-222222222222');

        $pairIds = [];

        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->method('exists')->willReturn(false);
        $linkRepository->expects($this->exactly(2))
            ->method('insert')
            ->willReturnCallback(function (string $trainerExternalId, string $sourceId, string $targetId, ?string $pairId) use (&$pairIds): void {
                $pairIds[] = $pairId;
            })
        ;

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);
        $trainerDexRepository->method('findOneBy')->willReturnOnConsecutiveCalls($source, $target);

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $service->create('trainer-1', 'national', 'shiny', true);

        $this->assertCount(2, $pairIds);
        $this->assertNotNull($pairIds[0]);
        $this->assertSame($pairIds[0], $pairIds[1]);
        $this->assertTrue(Uuid::isValid($pairIds[0]));
    }

    #[Test]
    public function deleteDelegatesToRepository(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->once())
            ->method('deleteByIdOrPairId')
            ->with('trainer-1', 'link-1')
        ;

        $trainerDexRepository = $this->createMock(TrainerDexRepository::class);

        $service = new TrainerDexLinkService($linkRepository, $trainerDexRepository);

        $service->delete('trainer-1', 'link-1');
    }

    private function trainerDexWithId(string $uuid): TrainerDex
    {
        $entity = new TrainerDex();

        $reflection = new \ReflectionProperty($entity, 'identifier');
        $reflection->setValue($entity, Uuid::fromString($uuid));

        return $entity;
    }
}
