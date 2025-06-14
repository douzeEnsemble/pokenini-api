<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\CalculateDexAvailabilities;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CalculateDexAvailabilities::class)]
class CalculateDexAvailabilitiesTest extends TestCase
{
    public function testSerialize(): void
    {
        $message = new CalculateDexAvailabilities('12');

        $this->assertEquals(
            'O:42:"App\Message\CalculateDexAvailabilities":1:{s:8:"actionId";s:2:"12";}',
            serialize($message)
        );
    }
}
