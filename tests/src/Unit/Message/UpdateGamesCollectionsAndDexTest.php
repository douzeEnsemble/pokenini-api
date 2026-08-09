<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\UpdateGamesCollectionsAndDex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UpdateGamesCollectionsAndDex::class)]
final class UpdateGamesCollectionsAndDexTest extends TestCase
{
    #[Test]
    public function serialize(): void
    {
        $message = new UpdateGamesCollectionsAndDex('12');

        $this->assertEquals(
            'O:40:"App\Message\UpdateGamesCollectionsAndDex":1:{s:8:"actionId";s:2:"12";}',
            serialize($message)
        );
    }
}
