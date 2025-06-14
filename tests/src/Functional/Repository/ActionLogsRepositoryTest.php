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

        $this->assertCount(15, $list);

        foreach ($list as $item) {
            $this->assertArrayHasKey('type_action', $item);
            $this->assertArrayHasKey('row_number', $item);
            $this->assertArrayHasKey('created_at', $item);
            $this->assertArrayHasKey('done_at', $item);
            $this->assertArrayHasKey('execution_time', $item);
            $this->assertArrayHasKey('details', $item);
            $this->assertArrayHasKey('error_trace', $item);
        }
    }
}
