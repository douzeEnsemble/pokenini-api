<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\TrainerDexAttributes;
use App\Repository\TrainerDexRepository;
use App\Tests\Common\Traits\CounterTrait\CountTrainerDexTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexRepository::class)]
final class TrainerDexRepositorySetTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountTrainerDexTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testExistingTrainerDex(): void
    {
        $repo = self::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $repo->set(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'redgreenblueyellow',
            new TrainerDexAttributes([])
        );

        $this->assertEquals(34, $this->getTrainerDexCount());
    }

    public function testNewTrainerDex(): void
    {
        $repo = self::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $repo->set(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'spoon',
            new TrainerDexAttributes([])
        );

        $this->assertEquals(35, $this->getTrainerDexCount());
    }

    public function testExistingCustomTrainerDex(): void
    {
        $repo = self::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $repo->set(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'homepogopokeball',
            new TrainerDexAttributes([])
        );

        $this->assertEquals(34, $this->getTrainerDexCount());
    }
}
