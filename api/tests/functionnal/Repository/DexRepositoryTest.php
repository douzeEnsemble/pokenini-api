<?php

namespace App\Tests\functionnal\Repository;

use App\Entity\Dex;
use App\Repository\DexRepository;
use App\Tests\resources\functionnal\CountDexTrait;
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

        $this->assertEquals('Gold, Silver, Crystal', $dexes[0]->name);
        $this->assertEquals(
            "(p.bankable or p.bankableish) and ba?.redgreenblueyellow",
            $dexes[2]->selectionRule
        );
    }

    public function testCountAll(): void
    {
        /** @var DexRepository $repo */
        $repo = static::getContainer()->get(DexRepository::class);

        $this->assertEquals($this->getDexCount(), $repo->countAll());
    }
}
