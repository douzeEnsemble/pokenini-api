<?php

declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use App\Entity\Dex;
use App\Entity\Region;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\ObjectManager;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DexTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private ?ObjectManager $entityManager = null;

    public function setUp(): void
    {
        $kernel = self::bootKernel();

        /** @var ?Registry */
        $doctrine = $kernel->getContainer()->get('doctrine');

        if (null !== $doctrine) {
            $this->entityManager = $doctrine->getManager();
        }
    }

    public function testChangeSlug(): void
    {
        $dex = $this->getDefaultDex();
        $this->saveDex($dex);

        $initLastChangedAt = $dex->lastChangedAt;

        $dex->slug = 'a_bunch';
        $this->saveDex($dex);

        $this->assertEquals($initLastChangedAt, $dex->lastChangedAt);
    }

    public function testChangeName(): void
    {
        $dex = $this->getDefaultDex();
        $this->saveDex($dex);

        $initLastChangedAt = $dex->lastChangedAt;

        $dex->name = 'A bunch';
        $this->saveDex($dex);

        $this->assertEquals($initLastChangedAt, $dex->lastChangedAt);
    }

    public function testChangeFrenchName(): void
    {
        $dex = $this->getDefaultDex();
        $this->saveDex($dex);

        $initLastChangedAt = $dex->lastChangedAt;

        $dex->name = 'Beaucoup';
        $this->saveDex($dex);

        $this->assertEquals($initLastChangedAt, $dex->lastChangedAt);
    }

    public function testChangeOrderNumber(): void
    {
        $dex = $this->getDefaultDex();
        $this->saveDex($dex);

        $initLastChangedAt = $dex->lastChangedAt;

        $dex->orderNumber = 1;
        $this->saveDex($dex);

        $this->assertEquals($initLastChangedAt, $dex->lastChangedAt);
    }

    public function testChangeDeletedAt(): void
    {
        $dex = $this->getDefaultDex();
        $this->saveDex($dex);

        $initLastChangedAt = $dex->lastChangedAt;

        $dex->setDeletedAt(new DateTime());
        $this->saveDex($dex);

        $this->assertEquals($initLastChangedAt, $dex->lastChangedAt);
    }

    public function testChangeSelectionRule(): void
    {
        $dex = $this->getDefaultDex();
        $this->saveDex($dex);

        $initLastChangedAt = $dex->lastChangedAt;

        $dex->selectionRule = 'is_available';
        $this->saveDex($dex);

        $this->assertGreaterThan($initLastChangedAt, $dex->lastChangedAt);
    }

    public function testChangeIsShiny(): void
    {
        $dex = $this->getDefaultDex();
        $this->saveDex($dex);

        $initLastChangedAt = $dex->lastChangedAt;

        $dex->isShiny = true;
        $this->saveDex($dex);

        $this->assertGreaterThan($initLastChangedAt, $dex->lastChangedAt);
    }

    public function testChangeIsPrivate(): void
    {
        $dex = $this->getDefaultDex();
        $this->saveDex($dex);

        $initLastChangedAt = $dex->lastChangedAt;

        $dex->isPrivate = true;
        $this->saveDex($dex);

        $this->assertEquals($initLastChangedAt, $dex->lastChangedAt);
    }

    public function testChangeIsDisplayForm(): void
    {
        $dex = $this->getDefaultDex();
        $this->saveDex($dex);

        $initLastChangedAt = $dex->lastChangedAt;

        $dex->isDisplayForm = true;
        $this->saveDex($dex);

        $this->assertGreaterThan($initLastChangedAt, $dex->lastChangedAt);
    }

    public function testChangeDisplayTemplate(): void
    {
        $dex = $this->getDefaultDex();
        $this->saveDex($dex);

        $initLastChangedAt = $dex->lastChangedAt;

        $dex->displayTemplate = 'list';
        $this->saveDex($dex);

        $this->assertGreaterThan($initLastChangedAt, $dex->lastChangedAt);
    }

    public function testChangeRegion(): void
    {
        $dex = $this->getDefaultDex();
        $this->saveDex($dex);

        $initLastChangedAt = $dex->lastChangedAt;

        $region = new Region();
        $this->entityManager?->persist($region);

        $dex->region = $region;
        $this->saveDex($dex);

        $this->assertGreaterThan($initLastChangedAt, $dex->lastChangedAt);
    }

    public function testChangeDescription(): void
    {
        $dex = $this->getDefaultDex();
        $this->saveDex($dex);

        $initLastChangedAt = $dex->lastChangedAt;

        $dex->description = 'A bunch of pokémons';
        $this->saveDex($dex);

        $this->assertEquals($initLastChangedAt, $dex->lastChangedAt);
    }

    public function testChangeFrenchDescription(): void
    {
        $dex = $this->getDefaultDex();
        $this->saveDex($dex);

        $initLastChangedAt = $dex->lastChangedAt;

        $dex->frenchDescription = 'Beaucoup de pokémons';
        $this->saveDex($dex);

        $this->assertEquals($initLastChangedAt, $dex->lastChangedAt);
    }

    public function testChangeIsReleased(): void
    {
        $dex = $this->getDefaultDex();
        $this->saveDex($dex);

        $initLastChangedAt = $dex->lastChangedAt;

        $dex->isReleased = true;
        $this->saveDex($dex);

        $this->assertEquals($initLastChangedAt, $dex->lastChangedAt);
    }

    public function testLastChangedAt(): void
    {
        $dex = $this->getDefaultDex();
        $this->saveDex($dex);

        $initLastChangedAt = $dex->lastChangedAt;

        $dex->lastChangedAt = new DateTime();
        $this->saveDex($dex);

        $this->assertNotEquals($initLastChangedAt, $dex->lastChangedAt);
    }

    private function getDefaultDex(): Dex
    {
        $dex = new Dex();

        $dex->slug = 'a_lot';
        $dex->name = 'A lot';
        $dex->frenchName = 'Plein';
        $dex->orderNumber = 0;
        $dex->setDeletedAt(null);

        $dex->selectionRule = 'isAvailable';
        $dex->isShiny = false;
        $dex->isPrivate = false;
        $dex->isDisplayForm = false;
        $dex->displayTemplate = 'box';
        $dex->region = null;
        $dex->description = 'A lot of pokémons';
        $dex->frenchDescription = 'Pleins de pokémons';
        $dex->isReleased = false;
        $dex->lastChangedAt = new \DateTime();

        return $dex;
    }

    private function saveDex(Dex $dex): void
    {
        $this->entityManager?->persist($dex);
        $this->entityManager?->flush();
    }
}
