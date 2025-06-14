<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\UpdatePokemons;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UpdatePokemons::class)]
class UpdatePokemonsTest extends TestCase
{
    public function testSerialize(): void
    {
        $message = new UpdatePokemons('12');

        $this->assertEquals(
            'O:26:"App\Message\UpdatePokemons":1:{s:8:"actionId";s:2:"12";}',
            serialize($message)
        );
    }
}
