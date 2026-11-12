<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * A catch state with its name in the language the client asked for.
 */
final class LocalizedCatchStateResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly string $color,
        #[SerializedName('text_color')]
        public readonly string $textColor,
    ) {}
}
