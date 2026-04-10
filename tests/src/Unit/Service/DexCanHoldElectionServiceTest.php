<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\DexQueryOptions;
use App\Repository\DexRepository;
use App\Service\DexCanHoldElectionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexCanHoldElectionService::class)]
final class DexCanHoldElectionServiceTest extends TestCase
{
    public function testGet(): void
    {
        $queryOptions = new DexQueryOptions([
            'include_unreleased_dex' => true,
            'include_premium_dex' => true,
        ]);

        $repository = $this->createMock(DexRepository::class);
        $repository->expects($this->once())
            ->method('getCanHoldElection')
            ->with($queryOptions)
            ->willReturn(
                [
                    [
                        'toto',
                    ],
                    [
                        'titi',
                    ],
                ],
            )
        ;

        $service = new DexCanHoldElectionService($repository);

        $this->assertEquals(
            [
                [
                    'toto',
                ],
                [
                    'titi',
                ],
            ],
            $service->get($queryOptions)
        );
    }
}
