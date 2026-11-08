<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\GameBundlesRepository;
use App\Tests\Common\Traits\LocalizedNamesRepositoryTestTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(GameBundlesRepository::class)]
final class GameBundlesRepositoryTest extends KernelTestCase
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
        $repo = self::getContainer()->get(GameBundlesRepository::class);

        $list = $repo->getAll();

        $this->assertCount(19, $list);
    }

    #[\Override]
    protected function getAllRows(string $locale, string $fallbackLocale): array
    {
        return self::getContainer()->get(GameBundlesRepository::class)->getAll($locale, $fallbackLocale);
    }

    #[\Override]
    protected function localizedTable(): string
    {
        return 'game_bundle';
    }

    #[\Override]
    protected function localizedExample(): array
    {
        return ['slug' => 'redgreenblueyellow', 'en' => 'Red, Green, Blue, Yellow', 'fr' => 'Rouge, Vert, Bleu, Jaune'];
    }
}
