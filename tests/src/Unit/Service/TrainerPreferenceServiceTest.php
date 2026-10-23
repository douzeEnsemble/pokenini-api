<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Exception\InvalidHomePageException;
use App\Repository\TrainerPreferenceRepository;
use App\Service\TrainerPreferenceService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerPreferenceService::class)]
final class TrainerPreferenceServiceTest extends TestCase
{
    #[Test]
    public function getHomePageReturnsTheSavedOne(): void
    {
        $repository = $this->createMock(TrainerPreferenceRepository::class);
        $repository->expects($this->once())
            ->method('getHomePage')
            ->with('trainer-1')
            ->willReturn('election')
        ;

        $service = new TrainerPreferenceService($repository);

        $this->assertSame('election', $service->getHomePage('trainer-1'));
    }

    #[Test]
    public function getHomePageDefaultsToTheHub(): void
    {
        $repository = $this->createMock(TrainerPreferenceRepository::class);
        $repository->expects($this->once())
            ->method('getHomePage')
            ->with('trainer-1')
            ->willReturn(null)
        ;

        $service = new TrainerPreferenceService($repository);

        $this->assertSame('hub', $service->getHomePage('trainer-1'));
    }

    #[Test]
    #[DataProvider('providerHomePages')]
    public function setHomePageSavesAKnownPage(string $homePage): void
    {
        $repository = $this->createMock(TrainerPreferenceRepository::class);
        $repository->expects($this->once())
            ->method('upsertHomePage')
            ->with('trainer-1', $homePage)
        ;

        $service = new TrainerPreferenceService($repository);

        $service->setHomePage('trainer-1', $homePage);
    }

    /**
     * @return string[][]
     */
    public static function providerHomePages(): array
    {
        return [
            'hub' => ['hub'],
            'album' => ['album'],
            'election' => ['election'],
            'game' => ['game'],
        ];
    }

    #[Test]
    public function setHomePageRejectsAnUnknownPage(): void
    {
        $repository = $this->createMock(TrainerPreferenceRepository::class);
        $repository->expects($this->never())->method('upsertHomePage');

        $service = new TrainerPreferenceService($repository);

        $this->expectException(InvalidHomePageException::class);

        $service->setHomePage('trainer-1', 'admin');
    }
}
