<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\CategoryFormsRepository;
use App\Tests\Common\Traits\LocalizedNamesRepositoryTestTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(CategoryFormsRepository::class)]
final class CategoryFormsRepositoryTest extends KernelTestCase
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
        $repo = self::getContainer()->get(CategoryFormsRepository::class);

        $list = $repo->getAll();

        $this->assertCount(3, $list);
    }

    #[\Override]
    protected function getAllRows(string $locale, string $fallbackLocale): array
    {
        return self::getContainer()->get(CategoryFormsRepository::class)->getAll($locale, $fallbackLocale);
    }

    #[\Override]
    protected function localizedTable(): string
    {
        return 'category_form';
    }

    #[\Override]
    protected function localizedExample(): array
    {
        return ['slug' => 'starter', 'en' => 'Starter', 'fr' => 'de Départ'];
    }
}
