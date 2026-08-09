<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\UpdateLabels;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UpdateLabels::class)]
final class UpdateLabelsTest extends TestCase
{
    #[Test]
    public function serialize(): void
    {
        $message = new UpdateLabels('12');

        $this->assertEquals(
            'O:24:"App\Message\UpdateLabels":1:{s:8:"actionId";s:2:"12";}',
            serialize($message)
        );
    }
}
