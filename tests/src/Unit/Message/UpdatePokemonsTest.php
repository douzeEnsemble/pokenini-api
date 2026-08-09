<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\UpdatePokemons;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UpdatePokemons::class)]
final class UpdatePokemonsTest extends TestCase
{
    #[Test]
    public function serialize(): void
    {
        $message = new UpdatePokemons('12');

        $this->assertEquals(
            'O:26:"App\Message\UpdatePokemons":1:{s:8:"actionId";s:2:"12";}',
            serialize($message)
        );
    }
}
