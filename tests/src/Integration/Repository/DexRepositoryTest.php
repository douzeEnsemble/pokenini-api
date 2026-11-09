<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\DexQueryOptions;
use App\Entity\Dex;
use App\Repository\DexRepository;
use App\Tests\Common\Traits\CounterTrait\CountDexTrait;
use Doctrine\DBAL\Connection;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(DexRepository::class)]
final class DexRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountDexTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function getAll(): void
    {
        $repo = self::getContainer()->get(DexRepository::class);

        /** @var Dex[] $dex */
        $dex = $repo->getQueryAll()->getResult();

        $this->assertCount(
            $this->getDexCount() - 1,
            $dex
        );

        $this->assertEquals('Red / Green / Blue / Yellow', $dex[0]->name);

        $this->assertEquals(
            '(p.bankable or p.bankableish) and ba?.rubysapphireemerald',
            $dex[2]->selectionRule
        );
    }

    #[Test]
    public function getData(): void
    {
        $repo = self::getContainer()->get(DexRepository::class);

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
            .'and p.specialForm === null and p.regionalForm === null',
            $dexGSC['selection_rule']
        );
        $this->assertArrayHasKey('is_private', $dexGSC);
        $this->assertTrue($dexGSC['is_private']);

        $this->assertEmpty($repo->getData('7b52009b64fd0a2a49e6d8a939753077792b0554', 'dexthatdoesntexists'));
    }

    #[Test]
    public function getBannerLayers(): void
    {
        $repo = self::getContainer()->get(DexRepository::class);

        $result = $repo->getBannerLayers();

        $bySlug = [];
        foreach ($result as $row) {
            $bySlug[$row['slug']] = $row['banner_layers'];
        }

        $this->assertArrayHasKey('goldsilvercrystal', $bySlug);
        $this->assertSame('shiny,mega', $bySlug['goldsilvercrystal']);
        $this->assertArrayNotHasKey('redgreenblueyellow', $bySlug);
        $this->assertArrayNotHasKey('home', $bySlug);
    }

    /**
     * @param string[] $expectedSlugs
     */
    #[Test]
    #[DataProvider('providerGetCanHoldElection')]
    public function getCanHoldElection(
        bool $includeUnreleasedDex,
        bool $includePremiumDex,
        array $expectedSlugs,
    ): void {
        $repo = self::getContainer()->get(DexRepository::class);

        $options = new DexQueryOptions([
            'include_unreleased_dex' => $includeUnreleasedDex,
            'include_premium_dex' => $includePremiumDex,
        ]);

        $list = $repo->getCanHoldElection($options);

        $this->assertCount(count($expectedSlugs), $list);

        $slugs = array_map(
            static function (array $item): string {
                /** @var scalar $slug */
                $slug = $item['slug'];

                return (string) $slug;
            },
            $list
        );

        $this->assertSame($expectedSlugs, $slugs);
    }

    /**
     * @return array<string, array{
     *  includeUnreleasedDex: bool,
     *  includePremiumDex: bool,
     *  expectedSlugs: array<int, string>,
     * }>
     */
    public static function providerGetCanHoldElection(): array
    {
        return [
            'true_true' => [
                'includeUnreleasedDex' => true,
                'includePremiumDex' => true,
                'expectedSlugs' => [
                    'homepogo',
                    'home',
                    'redgreenblueyellow',
                    'spoon',
                ],
            ],
            'false_true' => [
                'includeUnreleasedDex' => false,
                'includePremiumDex' => true,
                'expectedSlugs' => [
                    'home',
                    'redgreenblueyellow',
                ],
            ],
            'true_false' => [
                'includeUnreleasedDex' => true,
                'includePremiumDex' => false,
                'expectedSlugs' => [
                    'homepogo',
                    'home',
                ],
            ],
            'false_false' => [
                'includeUnreleasedDex' => false,
                'includePremiumDex' => false,
                'expectedSlugs' => [
                    'home',
                ],
            ],
        ];
    }

    #[Test]
    public function getDataInTheRequestedLocale(): void
    {
        $repo = self::getContainer()->get(DexRepository::class);

        $dex = $repo->getData('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'fr', 'en');

        $this->assertSame('Rouge / Vert / Bleu / Jaune', $dex['localized_name']);
        $this->assertSame('La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.', $dex['localized_description']);
        $this->assertSame('Kanto', $dex['region_localized_name']);
        // The English name stays, the legacy shape still needs it
        $this->assertSame('Red / Green / Blue / Yellow', $dex['name']);
    }

    #[Test]
    public function getDataInTheEnglishLocale(): void
    {
        $repo = self::getContainer()->get(DexRepository::class);

        $dex = $repo->getData('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'en', 'en');

        $this->assertSame('Red / Green / Blue / Yellow', $dex['localized_name']);
        $this->assertSame('The list of obtainable Pokémons in Red, Blue, Yellow and even Green games', $dex['localized_description']);
    }

    #[Test]
    public function getDataFallsBackToTheFallbackLocale(): void
    {
        $repo = self::getContainer()->get(DexRepository::class);

        $dex = $repo->getData('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'de', 'fr');

        $this->assertSame('Rouge / Vert / Bleu / Jaune', $dex['localized_name']);
        $this->assertSame('Kanto', $dex['region_localized_name']);
    }

    #[Test]
    public function getDataGivesThePriorityToTheNameOfTheTrainerDex(): void
    {
        self::getContainer()->get(Connection::class)->executeStatement(
            "UPDATE trainer_dex SET names = '{\"en\": \"My album\", \"fr\": \"Mon album\"}' WHERE slug = 'redgreenblueyellow' AND trainer_external_id = :trainer",
            ['trainer' => '7b52009b64fd0a2a49e6d8a939753077792b0554'],
        );

        $repo = self::getContainer()->get(DexRepository::class);

        $this->assertSame('Mon album', $repo->getData('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'fr', 'en')['localized_name']);
        // Another trainer has no trainer dex: the name of the dex
        $this->assertSame('Rouge / Vert / Bleu / Jaune', $repo->getData('unknowntrainer', 'redgreenblueyellow', 'fr', 'en')['localized_name']);
    }
}
