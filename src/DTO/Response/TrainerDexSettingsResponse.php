<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class TrainerDexSettingsResponse
{
    public function __construct(
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        public readonly string $slug,
        #[SerializedName('display_template')]
        public readonly string $displayTemplate,
    ) {}
}
