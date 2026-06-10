<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Dex;
use App\Entity\Region;
use App\Factory\DexDebugResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[CoversClass(DexDebugResponseFactory::class)]
final class DexDebugResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromDexWithRegionMapsAllFields(): void
    {
        $region = new Region();
        $region->slug = 'kanto';
        $region->name = 'Kanto';
        $region->frenchName = 'Kanto';
        $region->orderNumber = 1;

        $dex = new Dex();
        $dex->slug = 'redgreenblueyellow';
        $dex->name = 'Red/Green/Blue/Yellow';
        $dex->frenchName = 'Rouge/Vert/Bleu/Jaune';
        $dex->orderNumber = 1;
        $dex->selectionRule = '{"type":"all"}';
        $dex->isShiny = false;
        $dex->isPremium = true;
        $dex->isDisplayForm = false;
        $dex->displayTemplate = 'box';
        $dex->region = $region;
        $dex->description = 'First generation';
        $dex->frenchDescription = 'Première génération';
        $dex->isReleased = true;
        $dex->canHoldElection = false;
        $dex->lastChangedAt = new \DateTime('2024-01-15T10:30:00+00:00');
        $dex->electionOrderNumber = 5;

        $result = DexDebugResponseFactory::fromDex($dex);

        self::assertNull($result->identifier);
        self::assertSame('redgreenblueyellow', $result->slug);
        self::assertSame('Red/Green/Blue/Yellow', $result->name);
        self::assertSame('Rouge/Vert/Bleu/Jaune', $result->frenchName);
        self::assertSame(1, $result->orderNumber);
        self::assertSame('{"type":"all"}', $result->selectionRule);
        self::assertFalse($result->flags->isShiny);
        self::assertTrue($result->flags->isPremium);
        self::assertFalse($result->flags->isDisplayForm);
        self::assertTrue($result->flags->isReleased);
        self::assertFalse($result->flags->canHoldElection);
        self::assertSame('box', $result->displayTemplate);
        self::assertSame('First generation', $result->description);
        self::assertSame('Première génération', $result->frenchDescription);
        self::assertSame('2024-01-15T10:30:00+00:00', $result->lastChangedAt);
        self::assertSame(5, $result->electionOrderNumber);
        self::assertNull($result->deletedAt);

        self::assertNotNull($result->region);
        self::assertNull($result->region->identifier);
        self::assertSame('kanto', $result->region->slug);
        self::assertSame('Kanto', $result->region->name);
        self::assertSame('Kanto', $result->region->frenchName);
        self::assertSame(1, $result->region->orderNumber);
        self::assertNull($result->region->deletedAt);
    }

    #[Test]
    public function fromDexWithNullRegionSetsNullRegion(): void
    {
        $dex = new Dex();
        $dex->slug = 'home';
        $dex->name = 'Home';
        $dex->frenchName = 'Home';
        $dex->orderNumber = 99;
        $dex->selectionRule = '';
        $dex->isShiny = true;
        $dex->isPremium = false;
        $dex->isDisplayForm = true;
        $dex->displayTemplate = 'list';
        $dex->region = null;
        $dex->description = '';
        $dex->frenchDescription = '';
        $dex->isReleased = false;
        $dex->canHoldElection = true;
        $dex->lastChangedAt = new \DateTime('2024-06-01T00:00:00+00:00');
        $dex->electionOrderNumber = 0;

        $result = DexDebugResponseFactory::fromDex($dex);

        self::assertNull($result->region);
        self::assertTrue($result->flags->isShiny);
        self::assertFalse($result->flags->isPremium);
        self::assertTrue($result->flags->isDisplayForm);
        self::assertFalse($result->flags->isReleased);
        self::assertTrue($result->flags->canHoldElection);
    }

    #[Test]
    public function fromDexWithDeletedAtReturnsFormattedDate(): void
    {
        $dex = new Dex();
        $dex->slug = 'deleted-dex';
        $dex->name = 'Deleted Dex';
        $dex->frenchName = 'Pokédex Supprimé';
        $dex->orderNumber = 0;
        $dex->selectionRule = '';
        $dex->isShiny = false;
        $dex->isPremium = false;
        $dex->isDisplayForm = false;
        $dex->displayTemplate = 'box';
        $dex->description = '';
        $dex->frenchDescription = '';
        $dex->isReleased = false;
        $dex->canHoldElection = false;
        $dex->lastChangedAt = new \DateTime('2024-01-01T00:00:00+00:00');
        $dex->electionOrderNumber = 0;
        $dex->deletedAt = new \DateTime('2024-03-15T12:00:00+00:00');

        $result = DexDebugResponseFactory::fromDex($dex);

        self::assertSame('2024-03-15T12:00:00+00:00', $result->deletedAt);
    }

    #[Test]
    public function fromDexWithRegionDeletedAtReturnsFormattedDate(): void
    {
        $region = new Region();
        $region->slug = 'johto';
        $region->name = 'Johto';
        $region->frenchName = 'Johto';
        $region->orderNumber = 2;
        $region->deletedAt = new \DateTime('2024-04-20T08:00:00+00:00');

        $dex = new Dex();
        $dex->slug = 'goldsilvercrystal';
        $dex->name = 'Gold/Silver/Crystal';
        $dex->frenchName = 'Or/Argent/Cristal';
        $dex->orderNumber = 2;
        $dex->selectionRule = '';
        $dex->isShiny = false;
        $dex->isPremium = false;
        $dex->isDisplayForm = false;
        $dex->displayTemplate = 'box';
        $dex->region = $region;
        $dex->description = '';
        $dex->frenchDescription = '';
        $dex->isReleased = false;
        $dex->canHoldElection = false;
        $dex->lastChangedAt = new \DateTime('2024-01-01T00:00:00+00:00');
        $dex->electionOrderNumber = 0;

        $result = DexDebugResponseFactory::fromDex($dex);

        self::assertNotNull($result->region);
        self::assertSame('2024-04-20T08:00:00+00:00', $result->region->deletedAt);
    }

    #[Test]
    public function fromDexWithIdentifierReturnsUuidString(): void
    {
        $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');

        $dex = new Dex();
        $dex->slug = 'test-dex';
        $dex->name = 'Test Dex';
        $dex->frenchName = 'Pokédex Test';
        $dex->orderNumber = 0;
        $dex->selectionRule = '';
        $dex->isShiny = false;
        $dex->isPremium = false;
        $dex->isDisplayForm = false;
        $dex->displayTemplate = 'box';
        $dex->description = '';
        $dex->frenchDescription = '';
        $dex->isReleased = false;
        $dex->canHoldElection = false;
        $dex->lastChangedAt = new \DateTime('2024-01-01T00:00:00+00:00');
        $dex->electionOrderNumber = 0;

        $reflection = new \ReflectionProperty(Dex::class, 'identifier');
        $reflection->setValue($dex, $uuid);

        $result = DexDebugResponseFactory::fromDex($dex);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $result->identifier);
    }

    #[Test]
    public function fromDexWithRegionIdentifierReturnsUuidString(): void
    {
        $uuid = Uuid::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');

        $region = new Region();
        $region->slug = 'sinnoh';
        $region->name = 'Sinnoh';
        $region->frenchName = 'Sinnoh';
        $region->orderNumber = 4;

        $reflection = new \ReflectionProperty(Region::class, 'identifier');
        $reflection->setValue($region, $uuid);

        $dex = new Dex();
        $dex->slug = 'diamondpearl';
        $dex->name = 'Diamond/Pearl';
        $dex->frenchName = 'Diamant/Perle';
        $dex->orderNumber = 4;
        $dex->selectionRule = '';
        $dex->isShiny = false;
        $dex->isPremium = false;
        $dex->isDisplayForm = false;
        $dex->displayTemplate = 'box';
        $dex->region = $region;
        $dex->description = '';
        $dex->frenchDescription = '';
        $dex->isReleased = false;
        $dex->canHoldElection = false;
        $dex->lastChangedAt = new \DateTime('2024-01-01T00:00:00+00:00');
        $dex->electionOrderNumber = 0;

        $result = DexDebugResponseFactory::fromDex($dex);

        self::assertNotNull($result->region);
        self::assertSame('6ba7b810-9dad-11d1-80b4-00c04fd430c8', $result->region->identifier);
    }
}
