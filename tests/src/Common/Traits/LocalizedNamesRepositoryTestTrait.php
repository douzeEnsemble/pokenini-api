<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Test;

/**
 * The tests of a repository which gives the name of its records in the requested locale ("localized_name").
 *
 * The using class is a KernelTestCase which refreshes the database.
 */
trait LocalizedNamesRepositoryTestTrait
{
    #[Test]
    public function getAllInTheRequestedLocale(): void
    {
        $example = $this->localizedExample();

        $rows = $this->getAllRows('fr', 'en');

        $this->assertSame($example['fr'], $this->localizedNameOf($rows, $example['slug']));
    }

    #[Test]
    public function getAllFallsBackWhenTheLocaleHasNoName(): void
    {
        $example = $this->localizedExample();

        $rows = $this->getAllRows('de', 'fr');

        $this->assertSame($example['fr'], $this->localizedNameOf($rows, $example['slug']));
    }

    #[Test]
    public function getAllFallsBackWhenTheNameIsEmpty(): void
    {
        $example = $this->localizedExample();
        $this->setNames($example['slug'], json_encode(['en' => $example['en'], 'fr' => ''], JSON_THROW_ON_ERROR));

        $rows = $this->getAllRows('fr', 'en');

        $this->assertSame($example['en'], $this->localizedNameOf($rows, $example['slug']));
    }

    #[Test]
    public function getAllFallsBackToTheNameColumnAsALastResort(): void
    {
        $example = $this->localizedExample();
        $this->setNames($example['slug'], '{}');

        $rows = $this->getAllRows('fr', 'de');

        $this->assertSame($example['en'], $this->localizedNameOf($rows, $example['slug']));
    }

    /**
     * @return string[][] the rows of the repository for the given locales
     */
    abstract protected function getAllRows(string $locale, string $fallbackLocale): array;

    abstract protected function localizedTable(): string;

    /**
     * A record of the fixtures, with its names.
     *
     * @return array{slug: string, en: string, fr: string}
     */
    abstract protected function localizedExample(): array;

    private function setNames(string $slug, string $names): void
    {
        $table = $this->localizedTable();

        self::getContainer()->get(Connection::class)->executeStatement(
            "UPDATE {$table} SET names = CAST(:names AS jsonb) WHERE slug = :slug",
            ['names' => $names, 'slug' => $slug],
        );
    }

    /**
     * @param string[][] $rows
     */
    private function localizedNameOf(array $rows, string $slug): string
    {
        foreach ($rows as $row) {
            if ($slug === $row['slug']) {
                return $row['localized_name'];
            }
        }

        $this->fail("No record {$slug}");
    }
}
