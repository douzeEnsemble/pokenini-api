<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\CatchStatesRepository;
use App\Tests\Common\Traits\LocalizedNamesRepositoryTestTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(CatchStatesRepository::class)]
final class CatchStatesRepositoryTest extends KernelTestCase
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
        $repo = self::getContainer()->get(CatchStatesRepository::class);

        $list = $repo->getAll();

        $this->assertCount(4, $list);
    }

    #[\Override]
    protected function getAllRows(string $locale, string $fallbackLocale): array
    {
        return self::getContainer()->get(CatchStatesRepository::class)->getAll($locale, $fallbackLocale);
    }

    #[\Override]
    protected function localizedTable(): string
    {
        return 'catch_state';
    }

    #[\Override]
    protected function localizedExample(): array
    {
        return ['slug' => 'no', 'en' => 'No', 'fr' => 'Non'];
    }
}
