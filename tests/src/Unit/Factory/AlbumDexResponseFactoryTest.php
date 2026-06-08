<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\AlbumRegionResponse;
use App\Factory\AlbumDexResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumDexResponseFactory::class)]
final class AlbumDexResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowBuildsResponseWithCorrectScalarFields(): void
    {
        $result = AlbumDexResponseFactory::fromSqlRow($this->getRedGreenBlueYellowRow());

        self::assertSame('redgreenblueyellow', $result->slug);
        self::assertSame('redgreenblueyellow', $result->originalSlug);
        self::assertSame('Red / Green / Blue / Yellow', $result->name);
        self::assertSame('Rouge / Vert / Bleu / Jaune', $result->frenchName);
        self::assertSame('box', $result->displayTemplate);
        self::assertSame('(p.bankable or p.bankableish) and ba?.redgreenblueyellow', $result->selectionRule);
        self::assertSame(
            'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            $result->description,
        );
        self::assertSame(
            'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            $result->frenchDescription,
        );
        self::assertSame('20230221.085100', $result->version);
    }

    #[Test]
    public function fromSqlRowBuildsFlagsSubObjectForReleasedDex(): void
    {
        $result = AlbumDexResponseFactory::fromSqlRow($this->getRedGreenBlueYellowRow());

        self::assertFalse($result->flags->isShiny);
        self::assertFalse($result->flags->isPrivate);
        self::assertFalse($result->flags->isOnHome);
        self::assertTrue($result->flags->isDisplayForm);
        self::assertTrue($result->flags->isReleased);
        self::assertFalse($result->flags->isPremium);
        self::assertFalse($result->flags->isCustom);
    }

    #[Test]
    public function fromSqlRowBuildsFlagsSubObjectForPrivateDex(): void
    {
        $result = AlbumDexResponseFactory::fromSqlRow($this->getHomeRow());

        self::assertFalse($result->flags->isShiny);
        self::assertTrue($result->flags->isPrivate);
        self::assertFalse($result->flags->isOnHome);
        self::assertTrue($result->flags->isDisplayForm);
        self::assertTrue($result->flags->isReleased);
        self::assertFalse($result->flags->isPremium);
        self::assertFalse($result->flags->isCustom);
    }

    #[Test]
    public function fromSqlRowBuildsRegionSubObject(): void
    {
        $result = AlbumDexResponseFactory::fromSqlRow($this->getRedGreenBlueYellowRow());

        self::assertInstanceOf(AlbumRegionResponse::class, $result->region);
        self::assertSame('Kanto', $result->region->name);
        self::assertSame('Kanto', $result->region->frenchName);
    }

    #[Test]
    public function fromSqlRowSetsNullRegionWhenRegionNameIsNull(): void
    {
        $result = AlbumDexResponseFactory::fromSqlRow($this->getHomeRow());

        self::assertNull($result->region);
    }

    #[Test]
    public function fromSqlRowSetsNullRegionWhenRegionNameIsEmptyString(): void
    {
        $row = $this->getHomeRow();
        $row['region_name'] = '';
        $row['region_french_name'] = '';

        $result = AlbumDexResponseFactory::fromSqlRow($row);

        self::assertNull($result->region);
    }

    #[Test]
    public function fromSqlRowCastsStringAndBoolFields(): void
    {
        $row = $this->getRedGreenBlueYellowRow();
        $row['slug'] = 123;
        $row['original_slug'] = 456;
        $row['name'] = 789;
        $row['french_name'] = 101;
        $row['is_shiny'] = 0;
        $row['is_private'] = 1;
        $row['is_on_home'] = 0;
        $row['is_display_form'] = 1;
        $row['display_template'] = 202;
        $row['selection_rule'] = 303;
        $row['description'] = 404;
        $row['french_description'] = 505;
        $row['version'] = 606;
        $row['is_released'] = 1;
        $row['is_premium'] = 0;
        $row['is_custom'] = 0;

        $result = AlbumDexResponseFactory::fromSqlRow($row);

        self::assertSame('123', $result->slug);
        self::assertSame('456', $result->originalSlug);
        self::assertSame('789', $result->name);
        self::assertSame('101', $result->frenchName);
        self::assertFalse($result->flags->isShiny);
        self::assertTrue($result->flags->isPrivate);
        self::assertFalse($result->flags->isOnHome);
        self::assertTrue($result->flags->isDisplayForm);
        self::assertSame('202', $result->displayTemplate);
        self::assertSame('303', $result->selectionRule);
        self::assertSame('404', $result->description);
        self::assertSame('505', $result->frenchDescription);
        self::assertSame('606', $result->version);
        self::assertTrue($result->flags->isReleased);
        self::assertFalse($result->flags->isPremium);
        self::assertFalse($result->flags->isCustom);
    }

    #[Test]
    public function fromSqlRowCastsRegionFieldsToStrings(): void
    {
        $row = $this->getRedGreenBlueYellowRow();
        $row['region_name'] = 123;
        $row['region_french_name'] = 456;

        $result = AlbumDexResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumRegionResponse::class, $result->region);
        self::assertSame('123', $result->region->name);
        self::assertSame('456', $result->region->frenchName);
    }

    /**
     * @return array<string, mixed>
     */
    private function getRedGreenBlueYellowRow(): array
    {
        return [
            'slug' => 'redgreenblueyellow',
            'original_slug' => 'redgreenblueyellow',
            'name' => 'Red / Green / Blue / Yellow',
            'french_name' => 'Rouge / Vert / Bleu / Jaune',
            'is_shiny' => false,
            'is_private' => false,
            'is_on_home' => false,
            'is_display_form' => true,
            'display_template' => 'box',
            'region_name' => 'Kanto',
            'region_french_name' => 'Kanto',
            'selection_rule' => '(p.bankable or p.bankableish) and ba?.redgreenblueyellow',
            'description' => 'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            'french_description' => 'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            'version' => '20230221.085100',
            'is_released' => true,
            'is_premium' => false,
            'is_custom' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getHomeRow(): array
    {
        return [
            'slug' => 'home',
            'original_slug' => 'home',
            'name' => 'Home',
            'french_name' => 'Home',
            'is_shiny' => false,
            'is_private' => true,
            'is_on_home' => false,
            'is_display_form' => true,
            'display_template' => 'box',
            'region_name' => null,
            'region_french_name' => null,
            'selection_rule' => '',
            'description' => '',
            'french_description' => '',
            'version' => '20230421.123456',
            'is_released' => true,
            'is_premium' => false,
            'is_custom' => false,
        ];
    }
}
