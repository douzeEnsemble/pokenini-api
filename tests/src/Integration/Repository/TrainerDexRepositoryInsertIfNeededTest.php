<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\TrainerDexRepository;
use App\Tests\Common\Traits\CounterTrait\CountTrainerDexTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexRepository::class)]
final class TrainerDexRepositoryInsertIfNeededTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountTrainerDexTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testWasNeeded(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(12, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon');

        $this->assertEquals(13, $this->getTrainerDexCount());
    }

    public function testWasntNeeded(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(12, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'douze');

        $this->assertEquals(12, $this->getTrainerDexCount());
    }

    public function testWasNeededThenWasnt(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(12, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon');

        $this->assertEquals(13, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon');

        $this->assertEquals(13, $this->getTrainerDexCount());
    }

    public function testlugOkThenKo(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(12, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon');

        $this->assertEquals(13, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon');

        $this->assertEquals(13, $this->getTrainerDexCount());
    }
}
