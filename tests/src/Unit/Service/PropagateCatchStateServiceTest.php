<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\PokedexRepository;
use App\Repository\TrainerDexLinkRepository;
use App\Service\PropagateCatchStateService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PropagateCatchStateService::class)]
final class PropagateCatchStateServiceTest extends TestCase
{
    #[Test]
    public function propagatesToDirectNeighbourWhenValueChanges(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->exactly(2))
            ->method('getOutgoingEdges')
            ->willReturnMap([
                ['trainer-1', 'dex-a', [['target_trainer_dex_id' => 'dex-b', 'target_dex_slug' => 'b']]],
                ['trainer-1', 'dex-b', []],
            ])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->once())
            ->method('upsertIfDifferent')
            ->with('dex-b', 'pikachu', 'yes')
            ->willReturn(true)
        ;

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame(
            ['b'],
            $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes')
        );
    }

    #[Test]
    public function stopsAtANodeWhoseValueDidNotChange(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->once())
            ->method('getOutgoingEdges')
            ->with('trainer-1', 'dex-a')
            ->willReturn([['target_trainer_dex_id' => 'dex-b', 'target_dex_slug' => 'b']])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->once())
            ->method('upsertIfDifferent')
            ->with('dex-b', 'pikachu', 'yes')
            ->willReturn(false)
        ;

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame(
            [],
            $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes')
        );
    }

    #[Test]
    public function propagatesTransitivelyThroughAChain(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->exactly(3))
            ->method('getOutgoingEdges')
            ->willReturnMap([
                ['trainer-1', 'dex-a', [['target_trainer_dex_id' => 'dex-b', 'target_dex_slug' => 'b']]],
                ['trainer-1', 'dex-b', [['target_trainer_dex_id' => 'dex-c', 'target_dex_slug' => 'c']]],
                ['trainer-1', 'dex-c', []],
            ])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->exactly(2))
            ->method('upsertIfDifferent')
            ->willReturnMap([
                ['dex-b', 'pikachu', 'yes', true],
                ['dex-c', 'pikachu', 'yes', true],
            ])
        ;

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame(
            ['b', 'c'],
            $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes')
        );
    }

    #[Test]
    public function cycleTerminatesByIdempotenceWithoutInfiniteLoop(): void
    {
        // A <-> B: origin is A, edge A -> B, and B -> A also exists.
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->exactly(2))
            ->method('getOutgoingEdges')
            ->willReturnMap([
                ['trainer-1', 'dex-a', [['target_trainer_dex_id' => 'dex-b', 'target_dex_slug' => 'b']]],
                ['trainer-1', 'dex-b', [['target_trainer_dex_id' => 'dex-a', 'target_dex_slug' => 'a']]],
            ])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->exactly(2))
            ->method('upsertIfDifferent')
            ->willReturnMap([
                // B changes to the new value...
                ['dex-b', 'pikachu', 'yes', true],
                // ...but A, the origin, already has it (the caller already wrote it before calling propagate()) so the cycle stops here.
                ['dex-a', 'pikachu', 'yes', false],
            ])
        ;

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame(
            ['b'],
            $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes')
        );
    }

    #[Test]
    public function threeNodeCycleTerminatesByIdempotence(): void
    {
        // A -> B -> C -> A. Origin A was already written by the caller before propagate() runs,
        // so by the time the cycle comes back around to A, upsertIfDifferent('dex-a', ...) is a no-op.
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->exactly(3))
            ->method('getOutgoingEdges')
            ->willReturnMap([
                ['trainer-1', 'dex-a', [['target_trainer_dex_id' => 'dex-b', 'target_dex_slug' => 'b']]],
                ['trainer-1', 'dex-b', [['target_trainer_dex_id' => 'dex-c', 'target_dex_slug' => 'c']]],
                ['trainer-1', 'dex-c', [['target_trainer_dex_id' => 'dex-a', 'target_dex_slug' => 'a']]],
            ])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->exactly(3))
            ->method('upsertIfDifferent')
            ->willReturnMap([
                ['dex-b', 'pikachu', 'yes', true],
                ['dex-c', 'pikachu', 'yes', true],
                ['dex-a', 'pikachu', 'yes', false],
            ])
        ;

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame(
            ['b', 'c'],
            $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes')
        );
    }

    #[Test]
    public function pokemonAbsentFromAnIntermediateDexSkipsTheWriteButKeepsTraversing(): void
    {
        // A -> B -> C, pokemon isn't in B's DexAvailability (upsertIfDifferent returns false for that reason)
        // but traversal must still continue from B to C per the design.
        //
        // Per the design, a skipped write means the traversal from that node does NOT continue automatically
        // (propagation only enqueues a node's own outgoing edges when its upsertIfDifferent returned true) —
        // so C is never reached in this scenario, matching the "changed" flag being the sole continuation signal.
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->once())
            ->method('getOutgoingEdges')
            ->with('trainer-1', 'dex-a')
            ->willReturn([['target_trainer_dex_id' => 'dex-b', 'target_dex_slug' => 'b']])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->once())
            ->method('upsertIfDifferent')
            ->with('dex-b', 'pikachu', 'yes')
            ->willReturn(false)
        ;

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame(
            [],
            $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes')
        );
    }

    #[Test]
    public function fanOutContinuesToSiblingEdgeAfterANoChangeSibling(): void
    {
        // A -> B and A -> C are two sibling edges discovered together from A's single
        // expansion (both already queued before either is processed). B is idempotent
        // (no change), so the loop must `continue` to the still-queued sibling C rather
        // than abandon the whole traversal.
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->exactly(2))
            ->method('getOutgoingEdges')
            ->willReturnMap([
                ['trainer-1', 'dex-a', [
                    ['target_trainer_dex_id' => 'dex-b', 'target_dex_slug' => 'b'],
                    ['target_trainer_dex_id' => 'dex-c', 'target_dex_slug' => 'c'],
                ]],
                ['trainer-1', 'dex-c', []],
            ])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->exactly(2))
            ->method('upsertIfDifferent')
            ->willReturnMap([
                ['dex-b', 'pikachu', 'yes', false],
                ['dex-c', 'pikachu', 'yes', true],
            ])
        ;

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame(
            ['c'],
            $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes')
        );
    }

    #[Test]
    public function fanOutPreservesPendingSiblingEdgeWhenAppendingNewlyDiscoveredOnes(): void
    {
        // A -> B and A -> C are two sibling edges discovered together from A's single
        // expansion. B changes AND has its own further outgoing edge to D. The still-queued
        // sibling C must survive being merged with B's newly discovered edges — replacing
        // the queue instead of merging into it would silently drop C.
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->exactly(4))
            ->method('getOutgoingEdges')
            ->willReturnMap([
                ['trainer-1', 'dex-a', [
                    ['target_trainer_dex_id' => 'dex-b', 'target_dex_slug' => 'b'],
                    ['target_trainer_dex_id' => 'dex-c', 'target_dex_slug' => 'c'],
                ]],
                ['trainer-1', 'dex-b', [
                    ['target_trainer_dex_id' => 'dex-d', 'target_dex_slug' => 'd'],
                ]],
                ['trainer-1', 'dex-c', []],
                ['trainer-1', 'dex-d', []],
            ])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->exactly(3))
            ->method('upsertIfDifferent')
            ->willReturnMap([
                ['dex-b', 'pikachu', 'yes', true],
                ['dex-c', 'pikachu', 'yes', true],
                ['dex-d', 'pikachu', 'yes', true],
            ])
        ;

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame(
            ['b', 'c', 'd'],
            $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes')
        );
    }

    #[Test]
    public function noOutgoingEdgesReturnsEmptyList(): void
    {
        $linkRepository = $this->createMock(TrainerDexLinkRepository::class);
        $linkRepository->expects($this->once())
            ->method('getOutgoingEdges')
            ->with('trainer-1', 'dex-a')
            ->willReturn([])
        ;

        $pokedexRepository = $this->createMock(PokedexRepository::class);
        $pokedexRepository->expects($this->never())->method('upsertIfDifferent');

        $service = new PropagateCatchStateService($linkRepository, $pokedexRepository);

        $this->assertSame([], $service->propagate('trainer-1', 'dex-a', 'pikachu', 'yes'));
    }
}
