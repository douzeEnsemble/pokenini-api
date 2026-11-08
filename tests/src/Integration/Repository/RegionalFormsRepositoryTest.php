<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\RegionalFormsRepository;
use App\Tests\Common\Traits\LocalizedNamesRepositoryTestTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(RegionalFormsRepository::class)]
final class RegionalFormsRepositoryTest extends KernelTestCase
{
    use LocalizedNamesRepositoryTestTrait;
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function getAll(): void
    {
        $repo = self::getContainer()->get(RegionalFormsRepository::class);

        $list = $repo->getAll();

        $this->assertCount(3, $list);
    }

    #[\Override]
    protected function getAllRows(string $locale, string $fallbackLocale): array
    {
        return self::getContainer()->get(RegionalFormsRepository::class)->getAll($locale, $fallbackLocale);
    }

    #[\Override]
    protected function localizedTable(): string
    {
        return 'regional_form';
    }

    #[\Override]
    protected function localizedExample(): array
    {
        return ['slug' => 'alolan', 'en' => 'Alolan', 'fr' => 'd\'Alola'];
    }
}
