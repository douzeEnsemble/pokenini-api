<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\TypesRepository;
use Doctrine\DBAL\Connection;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(TypesRepository::class)]
final class TypesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function getAll(): void
    {
        $repo = self::getContainer()->get(TypesRepository::class);

        $list = $repo->getAll();

        $this->assertCount(18, $list);
        $this->assertSame('Fighting', $this->localizedNameOf($list, 'fighting'));
    }

    #[Test]
    public function getAllInTheRequestedLocale(): void
    {
        $repo = self::getContainer()->get(TypesRepository::class);

        $list = $repo->getAll('fr', 'en');

        $this->assertSame('Combat', $this->localizedNameOf($list, 'fighting'));
        $this->assertSame('Fighting', $list[1]['name']);
        $this->assertSame('Combat', $list[1]['french_name']);
    }

    #[Test]
    public function getAllFallsBackWhenTheLocaleHasNoName(): void
    {
        $repo = self::getContainer()->get(TypesRepository::class);

        $list = $repo->getAll('de', 'fr');

        $this->assertSame('Combat', $this->localizedNameOf($list, 'fighting'));
    }

    #[Test]
    public function getAllFallsBackWhenTheNameIsEmpty(): void
    {
        $this->setNames('fighting', '{"en": "Fighting", "fr": ""}');

        $list = self::getContainer()->get(TypesRepository::class)->getAll('fr', 'en');

        $this->assertSame('Fighting', $this->localizedNameOf($list, 'fighting'));
    }

    #[Test]
    public function getAllFallsBackToTheNameColumnAsALastResort(): void
    {
        $this->setNames('fighting', '{}');

        $list = self::getContainer()->get(TypesRepository::class)->getAll('fr', 'en');

        $this->assertSame('Fighting', $this->localizedNameOf($list, 'fighting'));
    }

    private function setNames(string $slug, string $names): void
    {
        self::getContainer()->get(Connection::class)->executeStatement(
            'UPDATE type SET names = CAST(:names AS jsonb) WHERE slug = :slug',
            ['names' => $names, 'slug' => $slug],
        );
    }

    /**
     * @param string[][] $list
     */
    private function localizedNameOf(array $list, string $slug): string
    {
        foreach ($list as $row) {
            if ($slug === $row['slug']) {
                return $row['localized_name'];
            }
        }

        $this->fail("No type {$slug}");
    }
}
