<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\ActionLog;
use App\Repository\ActionLogsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(ActionLogsRepository::class)]
final class ActionLogsRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function getLastests(): void
    {
        $repo = self::getContainer()->get(ActionLogsRepository::class);

        $list = $repo->getLastests();

        $this->assertCount(17, $list);

        foreach ($list as $item) {
            $this->assertArrayHasKey('type_action', $item);

            $this->assertArrayHasKey('row_number', $item);
            $this->assertThat($item['row_number'], $this->logicalOr(
                $this->isInt(),
                $this->isNull(),
            ));

            $this->assertArrayHasKey('created_at', $item);

            $this->assertArrayHasKey('done_at', $item);
            $this->assertThat($item['done_at'], $this->logicalOr(
                $this->isString(),
                $this->isNull(),
            ));

            $this->assertArrayHasKey('execution_time', $item);
            $this->assertThat($item['execution_time'], $this->logicalOr(
                $this->isString(),
                $this->isNull(),
            ));

            $this->assertArrayHasKey('details', $item);
            $this->assertThat($item['details'], $this->logicalOr(
                $this->isString(),
                $this->isNull(),
            ));

            $this->assertArrayHasKey('error_trace', $item);
            $this->assertThat($item['error_trace'], $this->logicalOr(
                $this->isString(),
                $this->isNull(),
            ));
        }
    }

    #[Test]
    public function hasPending(): void
    {
        $repo = self::getContainer()->get(ActionLogsRepository::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $since = new \DateTimeImmutable('-1 hour');

        $this->assertFalse($repo->hasPending('App\Test\Pending', $since));

        $old = new ActionLog('App\Test\Pending');
        $old->createdAt = new \DateTime('-2 hours');
        $done = new ActionLog('App\Test\Pending');
        $done->doneAt = new \DateTime();
        $em->persist($old);
        $em->persist($done);
        $em->flush();

        $this->assertFalse($repo->hasPending('App\Test\Pending', $since));

        $em->persist(new ActionLog('App\Test\Pending'));
        $em->flush();

        $this->assertTrue($repo->hasPending('App\Test\Pending', $since));
        $this->assertFalse($repo->hasPending('App\Test\Other', $since));
    }
}
