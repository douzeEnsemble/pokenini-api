<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\UpdateLabels;
use PHPUnit\Framework\TestCase;

class UpdateLabelsTest extends TestCase
{
    public function testSerialize(): void
    {
        $message = new UpdateLabels('12');

        $this->assertEquals(
            'O:24:"App\Message\UpdateLabels":1:{s:8:"actionId";s:2:"12";}',
            serialize($message)
        );
    }
}
