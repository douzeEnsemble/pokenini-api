<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Repository\LocalizedName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LocalizedName::class)]
final class LocalizedNameTest extends TestCase
{
    #[Test]
    public function sqlOfASingleTable(): void
    {
        $this->assertSame(
            "COALESCE(NULLIF(names ->> :locale, ''), NULLIF(names ->> :fallback_locale, ''), name)",
            LocalizedName::sql(),
        );
    }

    #[Test]
    public function sqlOfAnAliasedTable(): void
    {
        $this->assertSame(
            "COALESCE(NULLIF(gb.names ->> :locale, ''), NULLIF(gb.names ->> :fallback_locale, ''), gb.name)",
            LocalizedName::sql('gb'),
        );
    }

    #[Test]
    public function parameters(): void
    {
        $this->assertSame(
            ['locale' => 'fr', 'fallback_locale' => 'en'],
            LocalizedName::parameters('fr', 'en'),
        );
    }

    #[Test]
    public function sqlOfAnotherColumn(): void
    {
        $this->assertSame(
            "COALESCE(NULLIF(p.forms_labels ->> :locale, ''), NULLIF(p.forms_labels ->> :fallback_locale, ''), p.forms_label)",
            LocalizedName::sql('p', 'forms_labels', 'forms_label'),
        );
    }
}
