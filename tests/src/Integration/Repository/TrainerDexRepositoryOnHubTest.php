<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\DexQueryOptions;
use App\DTO\TrainerDexAttributes;
use App\Repository\TrainerDexRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexRepository::class)]
final class TrainerDexRepositoryOnHubTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function noneOnHub(): void
    {
        $repo = self::getContainer()->get(TrainerDexRepository::class);

        $this->assertSame(0, $repo->countOthersOnHub(self::TRAINER, 'home'));
    }

    #[Test]
    public function countsTheOthersOnly(): void
    {
        $repo = self::getContainer()->get(TrainerDexRepository::class);

        $onHub = new TrainerDexAttributes(['is_on_hub' => true]);

        // An existing trainer dex, a new one and a custom one
        $repo->set(self::TRAINER, 'redgreenblueyellow', $onHub);
        $repo->set(self::TRAINER, 'spoon', $onHub);
        $repo->set(self::TRAINER, 'homepogopokeball', $onHub);

        $this->assertSame(3, $repo->countOthersOnHub(self::TRAINER, 'home'));
        $this->assertSame(2, $repo->countOthersOnHub(self::TRAINER, 'spoon'));
        $this->assertSame(0, $repo->countOthersOnHub('5d1bf1f7e9ad9a3e4bc2e5b06e2c4d7c8a6e0bc9', 'home'));

        $repo->set(self::TRAINER, 'spoon', new TrainerDexAttributes([]));
        $repo->set(self::TRAINER, 'homepogopokeball', new TrainerDexAttributes([]));

        $this->assertSame(1, $repo->countOthersOnHub(self::TRAINER, 'home'));
        $this->assertSame(0, $repo->countOthersOnHub(self::TRAINER, 'redgreenblueyellow'));
    }

    #[Test]
    public function listShowsTheHubFlag(): void
    {
        $repo = self::getContainer()->get(TrainerDexRepository::class);

        $repo->set(self::TRAINER, 'redgreenblueyellow', new TrainerDexAttributes(['is_on_hub' => true]));

        $flags = [];
        foreach ($repo->getListQuery(self::TRAINER, new DexQueryOptions(['include_unreleased_dex' => true, 'include_premium_dex' => true])) as $row) {
            /** @var string $slug */
            $slug = $row['slug'];
            $flags[$slug] = $row['is_on_hub'];
        }

        $this->assertTrue($flags['redgreenblueyellow']);
        $this->assertFalse($flags['home']);
    }
}
