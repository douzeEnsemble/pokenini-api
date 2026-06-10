<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexDebugFlagsResponse;
use App\DTO\Response\DexDebugResponse;
use App\DTO\Response\RegionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexDebugResponse::class)]
final class DexDebugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $region = new RegionResponse(
            identifier: '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
            slug: 'kanto',
            name: 'Kanto',
            frenchName: 'Kanto',
            orderNumber: 1,
            deletedAt: null,
        );

        $flags = new DexDebugFlagsResponse(
            isShiny: false,
            isPremium: true,
            isDisplayForm: false,
            isReleased: true,
            canHoldElection: false,
        );

        $response = new DexDebugResponse(
            identifier: '550e8400-e29b-41d4-a716-446655440000',
            slug: 'redgreenblueyellow',
            name: 'Red/Green/Blue/Yellow',
            frenchName: 'Rouge/Vert/Bleu/Jaune',
            orderNumber: 1,
            selectionRule: '{"type":"all"}',
            flags: $flags,
            displayTemplate: 'box',
            region: $region,
            description: 'First generation',
            frenchDescription: 'Première génération',
            lastChangedAt: '2024-01-15T10:30:00+00:00',
            electionOrderNumber: 5,
            deletedAt: '2024-03-01T00:00:00+00:00',
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $response->identifier);
        self::assertSame('redgreenblueyellow', $response->slug);
        self::assertSame('Red/Green/Blue/Yellow', $response->name);
        self::assertSame('Rouge/Vert/Bleu/Jaune', $response->frenchName);
        self::assertSame(1, $response->orderNumber);
        self::assertSame('{"type":"all"}', $response->selectionRule);
        self::assertSame($flags, $response->flags);
        self::assertFalse($response->flags->isShiny);
        self::assertTrue($response->flags->isPremium);
        self::assertFalse($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->canHoldElection);
        self::assertSame('box', $response->displayTemplate);
        self::assertSame($region, $response->region);
        self::assertSame('First generation', $response->description);
        self::assertSame('Première génération', $response->frenchDescription);
        self::assertSame('2024-01-15T10:30:00+00:00', $response->lastChangedAt);
        self::assertSame(5, $response->electionOrderNumber);
        self::assertSame('2024-03-01T00:00:00+00:00', $response->deletedAt);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $flags = new DexDebugFlagsResponse(
            isShiny: true,
            isPremium: false,
            isDisplayForm: true,
            isReleased: false,
            canHoldElection: true,
        );

        $response = new DexDebugResponse(
            identifier: null,
            slug: 'home',
            name: 'Home',
            frenchName: 'Home',
            orderNumber: 99,
            selectionRule: '',
            flags: $flags,
            displayTemplate: 'list',
            region: null,
            description: '',
            frenchDescription: '',
            lastChangedAt: '2024-06-01T00:00:00+00:00',
            electionOrderNumber: 0,
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertNull($response->region);
        self::assertNull($response->deletedAt);
        self::assertTrue($response->flags->isShiny);
        self::assertFalse($response->flags->isPremium);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertFalse($response->flags->isReleased);
        self::assertTrue($response->flags->canHoldElection);
    }
}
