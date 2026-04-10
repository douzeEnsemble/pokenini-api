<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\UpdateRegionalDexNumbers;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UpdateRegionalDexNumbers::class)]
final class UpdateRegionalDexNumbersTest extends TestCase
{
    public function testSerialize(): void
    {
        $message = new UpdateRegionalDexNumbers('12');

        $this->assertEquals(
            'O:36:"App\Message\UpdateRegionalDexNumbers":1:{s:8:"actionId";s:2:"12";}',
            serialize($message)
        );
    }
}
