<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Entity\Dex;
use App\Repository\DexRepository;
use App\Tests\Resources\Traits\CounterTrait\CountDexTrait;
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

        $dexesIterator = $repo->getQueryAll();

        /** @var Dex[] $dexes */
        $dexes = iterator_to_array($dexesIterator->toIterable());

        $this->assertCount($this->getDexCount(), $dexes);

        $this->assertEquals('Red / Green / Blue / Yellow', $dexes[0]->name);

        $this->assertEquals(
            "(p.bankable or p.bankableish) and ba?.rubysapphireemerald",
            $dexes[2]->selectionRule
        );
    }

    public function testCountAll(): void
    {
        /** @var DexRepository $repo */
        $repo = static::getContainer()->get(DexRepository::class);

        $this->assertEquals($this->getDexCount(), $repo->countAll());
    }

    public function testGetBySlug(): void
    {
        /** @var DexRepository $repo */
        $repo = static::getContainer()->get(DexRepository::class);

        $dexRGBY = $repo->getBySlug('redgreenblueyellow');

        $this->assertEquals('Red / Green / Blue / Yellow', $dexRGBY?->name);
        $this->assertEquals(
            '(p.bankable or p.bankableish) and ba?.redgreenblueyellow',
            $dexRGBY?->selectionRule
        );

        $dexGSC = $repo->getBySlug('goldsilvercrystal');

        $this->assertEquals('Gold / Silver / Crystal', $dexGSC?->name);
        $this->assertEquals(
            '(p.bankable or p.bankableish) and ba?.goldsilvercrystal '
            . 'and p.specialForm === null and p.regionalForm === null',
            $dexGSC?->selectionRule
        );

        $this->assertNull($repo->getBySlug('dexthatdoesntexists'));
    }
}
