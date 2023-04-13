<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\TrainerDexRepository;
use App\Tests\Common\Traits\CounterTrait\CountTrainerDexTrait;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TrainerDexRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountTrainerDexTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testInsertIfNeededNoSlugOk(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(12, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon');

        $this->assertEquals(13, $this->getTrainerDexCount());
    }

    public function testInsertIfNeededSlugOk(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(12, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon', 'fork');

        $this->assertEquals(13, $this->getTrainerDexCount());
    }

    public function testInsertIfNeededNoSlugKoDexDontExists(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $this->expectException(NotNullConstraintViolationException::class);

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'douze');
    }

    public function testInsertIfNeededSlugKoDexDontExists(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $this->expectException(NotNullConstraintViolationException::class);

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'douze', 'treize');
    }

    public function testInsertIfNeededNoSlugKoAlreadyExists(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(12, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow');

        $this->assertEquals(12, $this->getTrainerDexCount());
    }

    public function testInsertIfNeededSlugKoAlreadyExists(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(12, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'homepogo', 'homepogoot');

        $this->assertEquals(12, $this->getTrainerDexCount());
    }

    public function testInsertIfNeededNoSlugOkThenKo(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(12, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon');

        $this->assertEquals(13, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon');

        $this->assertEquals(13, $this->getTrainerDexCount());
    }

    public function testInsertIfNeededlugOkThenKo(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $this->assertEquals(12, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon', 'fork');

        $this->assertEquals(13, $this->getTrainerDexCount());

        $repo->insertIfNeeded('7b52009b64fd0a2a49e6d8a939753077792b0554', 'spoon', 'fork');

        $this->assertEquals(13, $this->getTrainerDexCount());
    }
}
