<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\TrainerDexRepository;
use App\Tests\Common\Traits\CounterTrait\CountTrainerDexTrait;
use App\Tests\Common\Traits\GetterTrait\GetTrainerDexTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexRepository::class)]
final class TrainerDexRepositoryInsertIfNeededTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountTrainerDexTrait;
    use GetTrainerDexTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function wasNeeded(): void
    {
        $repo = self::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon');

        $this->assertEquals(35, $this->getTrainerDexCount());
    }

    #[Test]
    public function wasntNeeded(): void
    {
        $repo = self::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'douze');

        $this->assertEquals(34, $this->getTrainerDexCount());
    }

    #[Test]
    public function wasNeededThenWasnt(): void
    {
        $repo = self::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon');

        $this->assertEquals(35, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon');

        $this->assertEquals(35, $this->getTrainerDexCount());
    }

    #[Test]
    public function lugOkThenKo(): void
    {
        $repo = self::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon');

        $this->assertEquals(35, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon');

        $this->assertEquals(35, $this->getTrainerDexCount());
    }

    #[Test]
    public function theNamesAreThoseOfTheDex(): void
    {
        $repo = self::getContainer()->get(TrainerDexRepository::class);

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon');

        $names = $this->getTrainerDex('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon')['names'];

        $this->assertIsString($names);
        $this->assertSame(['en' => 'Spoon', 'fr' => 'Cuillière'], json_decode($names, associative: true));
    }
}
