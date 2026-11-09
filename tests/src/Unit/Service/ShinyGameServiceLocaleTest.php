<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\DexQueryOptions;
use App\DTO\ShinyGameAnswer;
use App\Repository\ShinyGameRepository;
use App\Service\ShinyGameService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

/**
 * The language of the names is given to the repository, with the fallback language.
 *
 * @internal
 */
#[CoversClass(ShinyGameService::class)]
final class ShinyGameServiceLocaleTest extends TestCase
{
    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';
    private const string GAME_ID = '01a15aac-cfd2-7085-bbc7-88594aa005dd';

    private MockObject&ShinyGameRepository $repository;
    private ShinyGameService $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->repository = $this->createMock(ShinyGameRepository::class);
        $this->service = new ShinyGameService(
            $this->repository,
            new Randomizer(new Xoshiro256StarStar(12)),
            'de',
        );
    }

    #[DataProvider('providerLocales')]
    #[Test]
    public function getPlayableDexList(?string $locale): void
    {
        $options = new DexQueryOptions();

        $this->repository
            ->expects($this->once())
            ->method('getPlayableDexList')
            ->with($options, $locale, 'de')
            ->willReturn([['slug' => 'demo']])
        ;

        $this->assertSame([['slug' => 'demo']], $this->service->getPlayableDexList($options, $locale));
    }

    #[DataProvider('providerLocales')]
    #[Test]
    public function getInProgressGamesByDex(?string $locale): void
    {
        $this->repository
            ->expects($this->once())
            ->method('getInProgressGamesByDex')
            ->with(self::TRAINER, $locale, 'de')
            ->willReturn(['demo' => ['id' => self::GAME_ID]])
        ;

        $this->assertSame(
            ['demo' => ['id' => self::GAME_ID]],
            $this->service->getInProgressGamesByDex(self::TRAINER, $locale),
        );
    }

    #[DataProvider('providerLocales')]
    #[Test]
    public function getGames(?string $locale): void
    {
        $this->repository
            ->expects($this->once())
            ->method('getGames')
            ->with(self::TRAINER, $locale, 'de')
            ->willReturn([['id' => self::GAME_ID]])
        ;

        $this->assertSame([['id' => self::GAME_ID]], $this->service->getGames(self::TRAINER, $locale));
    }

    #[DataProvider('providerLocales')]
    #[Test]
    public function get(?string $locale): void
    {
        $this->repository
            ->expects($this->once())
            ->method('getGame')
            ->with(self::TRAINER, self::GAME_ID, $locale, 'de')
            ->willReturn(['id' => self::GAME_ID])
        ;
        $this->repository
            ->expects($this->once())
            ->method('getNextRound')
            ->with(self::GAME_ID, $locale, 'de')
            ->willReturn(['position' => 1])
        ;

        $this->assertSame(
            ['game' => ['id' => self::GAME_ID], 'round' => ['position' => 1]],
            $this->service->get(self::TRAINER, self::GAME_ID, $locale),
        );
    }

    #[DataProvider('providerLocales')]
    #[Test]
    public function getRounds(?string $locale): void
    {
        $this->repository
            ->expects($this->once())
            ->method('getGame')
            ->with(self::TRAINER, self::GAME_ID, $locale, 'de')
            ->willReturn(['id' => self::GAME_ID])
        ;
        $this->repository
            ->expects($this->once())
            ->method('getAnsweredRounds')
            ->with(self::GAME_ID, $locale, 'de')
            ->willReturn([['position' => 1]])
        ;

        $this->assertSame(
            ['game' => ['id' => self::GAME_ID], 'rounds' => [['position' => 1]]],
            $this->service->getRounds(self::TRAINER, self::GAME_ID, $locale),
        );
    }

    #[DataProvider('providerLocales')]
    #[Test]
    public function create(?string $locale): void
    {
        $this->repository->method('getPlayableDexId')->willReturn('dex-id');
        $this->repository->method('getPlayablePokemonIds')->willReturn(['p1']);
        $this->repository->expects($this->once())->method('startGame');
        $this->repository
            ->expects($this->once())
            ->method('getGame')
            ->with(self::TRAINER, $this->anything(), $locale, 'de')
            ->willReturn(['id' => 'new-game'])
        ;

        $this->assertSame(['id' => 'new-game'], $this->service->create(self::TRAINER, 'demo', null, $locale));
    }

    #[DataProvider('providerLocales')]
    #[Test]
    public function answer(?string $locale): void
    {
        $this->repository
            ->expects($this->exactly(2))
            ->method('getGame')
            ->willReturnCallback(static fn (mixed ...$arguments): array => [
                'status' => 'in_progress',
                'locale' => $arguments[2],
                'fallback' => $arguments[3],
            ])
        ;
        $this->repository->method('getRoundAt')->willReturn(['chosen_shiny' => null, 'is_shiny' => true]);
        $this->repository->method('answerRound')->willReturn(true);

        $result = $this->service->answer(
            self::TRAINER,
            self::GAME_ID,
            new ShinyGameAnswer(['position' => 2, 'is_shiny' => true]),
            $locale,
        );

        $this->assertSame(['status' => 'in_progress', 'locale' => $locale, 'fallback' => 'de'], $result['game']);
    }

    /**
     * @return array<string, array{null|string}>
     */
    public static function providerLocales(): array
    {
        return ['no language' => [null], 'a language' => ['fr']];
    }
}
