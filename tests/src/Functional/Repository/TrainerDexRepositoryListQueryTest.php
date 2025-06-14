<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\DTO\DexQueryOptions;
use App\Repository\TrainerDexRepository;
use App\Tests\Common\Traits\CounterTrait\CountTrainerDexTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexRepository::class)]
class TrainerDexRepositoryListQueryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountTrainerDexTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetListQueryDefault(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $dexQueryOptions = new DexQueryOptions();

        $list = $repo->getListQuery('7b52009b64fd0a2a49e6d8a939753077792b0554', $dexQueryOptions);

        $this->assertInstanceOf(\Generator::class, $list);
        $this->assertCount(6, iterator_to_array($list, false));
    }

    public function testGetListQueryWithUnreleased(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $dexQueryOptions = new DexQueryOptions(['include_unreleased_dex' => true]);

        $list = $repo->getListQuery('7b52009b64fd0a2a49e6d8a939753077792b0554', $dexQueryOptions);

        $this->assertInstanceOf(\Generator::class, $list);
        $this->assertCount(10, iterator_to_array($list, false));
    }

    public function testGetListQueryWithPremium(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $dexQueryOptions = new DexQueryOptions(['include_premium_dex' => true]);

        $list = $repo->getListQuery('7b52009b64fd0a2a49e6d8a939753077792b0554', $dexQueryOptions);

        $this->assertInstanceOf(\Generator::class, $list);
        $this->assertCount(7, iterator_to_array($list, false));
    }

    public function testGetListQueryWithUnreleasedAndPremium(): void
    {
        /** @var TrainerDexRepository $repo */
        $repo = static::getContainer()->get(TrainerDexRepository::class);

        $dexQueryOptions = new DexQueryOptions([
            'include_unreleased_dex' => true,
            'include_premium_dex' => true,
        ]);

        $list = $repo->getListQuery('7b52009b64fd0a2a49e6d8a939753077792b0554', $dexQueryOptions);

        $this->assertInstanceOf(\Generator::class, $list);
        $this->assertCount(11, iterator_to_array($list, false));
    }
}
