<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class PokemonLabelsResponse
{
    public function __construct(
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        #[SerializedName('simplified_name')]
        public readonly ?string $simplifiedName,
        #[SerializedName('simplified_french_name')]
        public readonly ?string $simplifiedFrenchName,
        #[SerializedName('forms_label')]
        public readonly ?string $formsLabel,
        #[SerializedName('forms_french_label')]
        public readonly ?string $formsFrenchLabel,
    ) {}
}
