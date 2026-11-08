<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\CollectionsRepository;
use App\Tests\Common\Traits\LocalizedNamesRepositoryTestTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(CollectionsRepository::class)]
final class CollectionsRepositoryTest extends KernelTestCase
{
    use LocalizedNamesRepositoryTestTrait;
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function getAllSlug(): void
    {
        $repo = self::getContainer()->get(CollectionsRepository::class);

        $list = $repo->getAllSlugs();

        $this->assertEquals(
            [
                'swshdynamaxadventuresbosses',
                'svmassoutbreakspaldea',
                'svmassoutbreakskitakami',
                'svmassoutbreaksterrarium',
                'svtransferable',
                'pogoshadow',
                'pogoshadowshiny',
                'pogodynamax',
            ],
            $list
        );
    }

    #[Test]
    public function getAll(): void
    {
        $repo = self::getContainer()->get(CollectionsRepository::class);

        $list = $repo->getAll();

        $this->assertCount(8, $list);
    }

    #[\Override]
    protected function getAllRows(string $locale, string $fallbackLocale): array
    {
        return self::getContainer()->get(CollectionsRepository::class)->getAll($locale, $fallbackLocale);
    }

    #[\Override]
    protected function localizedTable(): string
    {
        return 'collection';
    }

    #[\Override]
    protected function localizedExample(): array
    {
        return ['slug' => 'swshdynamaxadventuresbosses', 'en' => 'Sword, Shield - Dynamax Adventures bosses', 'fr' => 'Sword, Shield - Boss des expéditions Dynamax'];
    }
}
