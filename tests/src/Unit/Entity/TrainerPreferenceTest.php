<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\TrainerPreference;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerPreference::class)]
final class TrainerPreferenceTest extends TestCase
{
    #[Test]
    public function defaults(): void
    {
        $entity = new TrainerPreference();

        $this->assertNull($entity->getIdentifier());
        $this->assertSame('', $entity->trainerExternalId);
        $this->assertSame('hub', $entity->homePage);
    }
}
