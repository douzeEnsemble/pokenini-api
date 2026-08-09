<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\ElectionReportQueryOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @internal
 */
#[CoversClass(ElectionReportQueryOptions::class)]
final class ElectionReportQueryOptionsTest extends TestCase
{
    #[Test]
    public function defaults(): void
    {
        $options = new ElectionReportQueryOptions();

        $this->assertSame('', $options->electionSlug);
        $this->assertSame(5, $options->count);
    }

    #[Test]
    public function explicitValues(): void
    {
        $options = new ElectionReportQueryOptions([
            'election_slug' => 'favorite',
            'count' => '10',
        ]);

        $this->assertSame('favorite', $options->electionSlug);
        $this->assertSame(10, $options->count);
    }

    #[Test]
    public function invalidElectionSlugType(): void
    {
        $this->expectException(InvalidOptionsException::class);

        new ElectionReportQueryOptions(['election_slug' => 12]);
    }

    #[Test]
    public function unknownOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        new ElectionReportQueryOptions(['unknown' => 'value']);
    }

    #[Test]
    public function invalidCountType(): void
    {
        $this->expectException(InvalidOptionsException::class);

        /**
         * @psalm-suppress InvalidArgument
         *
         * @phpstan-ignore argument.type
         */
        new ElectionReportQueryOptions(['count' => 5.4]);
    }
}
