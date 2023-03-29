<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Entity\Dex;
use App\Repository\DexRepository;
use App\Tests\Common\Traits\CounterTrait\CountDexTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DexRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountDexTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetAll(): void
    {
        /** @var DexRepository $repo */
        $repo = static::getContainer()->get(DexRepository::class);

        $dexIterator = $repo->getQueryAll();

        /** @var Dex[] $dex */
        $dex = iterator_to_array($dexIterator->toIterable());

        $this->assertCount(
            $this->getDexCount() - 1,
            $dex
        );

        $this->assertEquals('Red / Green / Blue / Yellow', $dex[0]->name);

        $this->assertEquals(
            "(p.bankable or p.bankableish) and ba?.rubysapphireemerald",
            $dex[2]->selectionRule
        );
    }

    public function testCountAll(): void
    {
        /** @var DexRepository $repo */
        $repo = static::getContainer()->get(DexRepository::class);

        $this->assertEquals(
            $this->getDexCount() - 1,
            $repo->countAll()
        );
    }

    public function testGetData(): void
    {
        /** @var DexRepository $repo */
        $repo = static::getContainer()->get(DexRepository::class);

        $dexRGBY = $repo->getData('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow');

        $this->assertArrayHasKey('name', $dexRGBY);
        $this->assertEquals('Red / Green / Blue / Yellow', $dexRGBY['name']);
        $this->assertArrayHasKey('selection_rule', $dexRGBY);
        $this->assertEquals(
            '(p.bankable or p.bankableish) and ba?.redgreenblueyellow',
            $dexRGBY['selection_rule']
        );
        $this->assertArrayHasKey('is_private', $dexRGBY);
        $this->assertFalse($dexRGBY['is_private']);

        $dexGSC = $repo->getData('7b52009b64fd0a2a49e6d8a939753077792b0554', 'goldsilvercrystal');

        $this->assertArrayHasKey('name', $dexGSC);
        $this->assertEquals('Gold / Silver / Crystal', $dexGSC['name']);
        $this->assertArrayHasKey('selection_rule', $dexGSC);
        $this->assertEquals(
            '(p.bankable or p.bankableish) and ba?.goldsilvercrystal '
            . 'and p.specialForm === null and p.regionalForm === null',
            $dexGSC['selection_rule']
        );
        $this->assertArrayHasKey('is_private', $dexGSC);
        $this->assertTrue($dexGSC['is_private']);

        $this->assertEmpty($repo->getData('7b52009b64fd0a2a49e6d8a939753077792b0554', 'dexthatdoesntexists'));
    }
}
