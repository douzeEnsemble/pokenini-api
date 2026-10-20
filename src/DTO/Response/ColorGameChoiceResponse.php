<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ColorGameChoiceResponse
{
    /**
     * @param string[] $codes
     * @param string[] $names
     * @param string[] $frenchNames
     */
    public function __construct(
        public readonly int $index,
        public readonly array $codes,
        public readonly array $names,
        #[SerializedName('french_names')]
        public readonly array $frenchNames,
    ) {}
}
