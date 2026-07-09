<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\ElectionReportQueryOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @internal
 */
#[CoversClass(ElectionReportQueryOptions::class)]
final class ElectionReportQueryOptionsTest extends TestCase
{
    public function testDefaults(): void
    {
        $options = new ElectionReportQueryOptions();

        $this->assertSame('', $options->electionSlug);
        $this->assertSame(5, $options->count);
    }

    public function testExplicitValues(): void
    {
        $options = new ElectionReportQueryOptions([
            'election_slug' => 'favorite',
            'count' => '10',
        ]);

        $this->assertSame('favorite', $options->electionSlug);
        $this->assertSame(10, $options->count);
    }

    public function testInvalidElectionSlugType(): void
    {
        $this->expectException(InvalidOptionsException::class);

        new ElectionReportQueryOptions(['election_slug' => 12]);
    }

    public function testUnknownOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        new ElectionReportQueryOptions(['unknown' => 'value']);
    }
}
