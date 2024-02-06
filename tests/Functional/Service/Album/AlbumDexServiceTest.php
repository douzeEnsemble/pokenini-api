<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service\Album;

use App\Service\Album\AlbumDexService;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AlbumDexServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleAvailabilityTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGet(): void
    {
        /** @var AlbumDexService $service */
        $service = static::getContainer()->get(AlbumDexService::class);

        $dexRGBY = $service->get('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow');

        $this->assertArrayHasKey('name', $dexRGBY);
        $this->assertEquals('Red / Green / Blue / Yellow', $dexRGBY['name']);
        $this->assertArrayHasKey('selection_rule', $dexRGBY);
        $this->assertEquals(
            '(p.bankable or p.bankableish) and ba?.redgreenblueyellow',
            $dexRGBY['selection_rule']
        );
        $this->assertArrayHasKey('is_private', $dexRGBY);
        $this->assertFalse($dexRGBY['is_private']);

        $dexGSC = $service->get('7b52009b64fd0a2a49e6d8a939753077792b0554', 'goldsilvercrystal');

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

        $this->assertEmpty($service->get('7b52009b64fd0a2a49e6d8a939753077792b0554', 'dexthatdoesntexists'));
    }
}
