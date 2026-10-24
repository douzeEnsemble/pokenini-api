<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater;

use App\Tests\Common\Traits\GetterTrait\GetDexTrait;
use App\Updater\AbstractUpdater;
use App\Updater\DexUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(DexUpdater::class)]
#[CoversClass(AbstractUpdater::class)]
final class DexUpdaterTest extends AbstractTestUpdater
{
    use GetDexTrait;

    protected int $initialTotalCount = 9;
    protected int $finalTotalCount = 25;
    protected int $initialDeletedTotalCount = 1;
    protected int $mustBeDeletedTotalCount = 4;
    protected string $sheetName = 'Dex';
    protected string $tableName = 'dex';

    #[Test]
    public function dexRegion(): void
    {
        $redGreenBlueYellowBefore = $this->getDexFromSlug('redgreenblueyellow');
        $rubySapphireEmeraldBefore = $this->getDexFromSlug('rubysapphireemerald');

        $this->assertEquals('Kanto', $redGreenBlueYellowBefore['region_name']);
        $this->assertNull($rubySapphireEmeraldBefore['region_name']);

        $this->getService()->execute('Dex');

        $redGreenBlueYellowAfter = $this->getDexFromSlug('redgreenblueyellow');
        $rubySapphireEmeraldAfter = $this->getDexFromSlug('rubysapphireemerald');

        $this->assertEquals('Kanto', $redGreenBlueYellowAfter['region_name']);
        $this->assertEquals('Hoenn', $rubySapphireEmeraldAfter['region_name']);
    }

    #[Test]
    public function dexIsReleased(): void
    {
        $redGreenBlueYellowBefore = $this->getDexFromSlug('redgreenblueyellow');
        $rubySapphireEmeraldBefore = $this->getDexFromSlug('rubysapphireemerald');

        $this->assertTrue($redGreenBlueYellowBefore['is_released']);
        $this->assertTrue($rubySapphireEmeraldBefore['is_released']);

        $this->getService()->execute('Dex');

        $redGreenBlueYellowAfter = $this->getDexFromSlug('redgreenblueyellow');
        $rubySapphireEmeraldAfter = $this->getDexFromSlug('rubysapphireemerald');

        $this->assertFalse($redGreenBlueYellowAfter['is_released']);
        $this->assertTrue($rubySapphireEmeraldAfter['is_released']);
    }

    #[Test]
    public function dexBannerLayers(): void
    {
        $xyBefore = $this->getDexFromSlug('xy');
        $redGreenBlueYellowBefore = $this->getDexFromSlug('redgreenblueyellow');

        $this->assertNull($xyBefore['banner_layers']);
        $this->assertNull($redGreenBlueYellowBefore['banner_layers']);

        $this->getService()->execute('Dex');

        $xyAfter = $this->getDexFromSlug('xy');
        $redGreenBlueYellowAfter = $this->getDexFromSlug('redgreenblueyellow');

        $this->assertSame('xy,shiny', $xyAfter['banner_layers']);
        $this->assertNull($redGreenBlueYellowAfter['banner_layers']);
    }

    #[Test]
    public function dexCanBeColorPlay(): void
    {
        $this->assertTrue($this->getDexFromSlug('redgreenblueyellow')['can_be_color_play']);
        $this->assertFalse($this->getDexFromSlug('goldsilvercrystal')['can_be_color_play']);
        $this->assertFalse($this->getDexFromSlug('rubysapphireemerald')['can_be_color_play']);
        $this->assertFalse($this->getDexFromSlug('home')['can_be_color_play']);

        $this->getService()->execute('Dex');

        $this->assertTrue($this->getDexFromSlug('redgreenblueyellow')['can_be_color_play']);
        $this->assertFalse($this->getDexFromSlug('goldsilvercrystal')['can_be_color_play']);
        $this->assertFalse($this->getDexFromSlug('rubysapphireemerald')['can_be_color_play']);
        $this->assertTrue($this->getDexFromSlug('home')['can_be_color_play']);
    }

    #[Test]
    public function dexGameOrder(): void
    {
        $this->assertEquals(3, $this->getDexFromSlug('redgreenblueyellow')['game_order_number']);

        $this->getService()->execute('Dex');

        $this->assertEquals(5, $this->getDexFromSlug('redgreenblueyellow')['game_order_number']);
        $this->assertEquals(1, $this->getDexFromSlug('goldsilvercrystal')['game_order_number']);
        $this->assertEquals(2, $this->getDexFromSlug('home')['game_order_number']);
        $this->assertEquals(0, $this->getDexFromSlug('rubysapphireemerald')['game_order_number']);
    }

    #[Test]
    public function dexCategory(): void
    {
        $this->assertEquals('main', $this->getDexFromSlug('redgreenblueyellow')['category_slug']);
        $this->assertEquals('home', $this->getDexFromSlug('home')['category_slug']);
        $this->assertNull($this->getDexFromSlug('demo')['category_slug']);

        $this->getService()->execute('Dex');

        $this->assertEquals('main', $this->getDexFromSlug('redgreenblueyellow')['category_slug']);
        $this->assertEquals('main', $this->getDexFromSlug('fireredleafgreen')['category_slug']);
        $this->assertEquals('home', $this->getDexFromSlug('homepokemongo')['category_slug']);
        $this->assertNull($this->getDexFromSlug('xy')['category_slug']);
        // The "special" category is not in the database before its own sheet is read
        $this->assertNull($this->getDexFromSlug('mega')['category_slug']);
    }

    #[\Override]
    protected function getService(): AbstractUpdater
    {
        /** @var DexUpdater */
        return self::getContainer()->get(DexUpdater::class);
    }
}
