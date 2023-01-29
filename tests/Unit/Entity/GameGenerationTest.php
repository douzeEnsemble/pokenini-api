<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\GameGeneration;
use PHPUnit\Framework\TestCase;

class GameGenerationTest extends TestCase
{
    public function testGetNumber(): void
    {
        $generation = new GameGeneration();
        $generation->name = '12';

        $this->assertSame(12, $generation->getNumber());
    }
}
