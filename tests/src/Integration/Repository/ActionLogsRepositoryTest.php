<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\ActionLogsRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(ActionLogsRepository::class)]
class ActionLogsRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetLastests(): void
    {
        /** @var ActionLogsRepository $repo */
        $repo = static::getContainer()->get(ActionLogsRepository::class);

        $list = $repo->getLastests();

        $this->assertCount(17, $list);

        foreach ($list as $item) {
            $this->assertArrayHasKey('type_action', $item);
            $this->assertIsString($item['type_action']);

            $this->assertArrayHasKey('row_number', $item);
            $this->assertThat($item['row_number'], $this->logicalOr(
                $this->isInt(),
                $this->isNull(),
            ));

            $this->assertArrayHasKey('created_at', $item);
            $this->assertIsString($item['created_at']);

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
}
