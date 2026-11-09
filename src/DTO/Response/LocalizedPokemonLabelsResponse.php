<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * The names of a Pokémon: its name and its simplified name in every language (the sheet of a
 * Pokémon lists them all), its forms label in the language the client asked for.
 */
final class LocalizedPokemonLabelsResponse
{
    /**
     * @param array<array-key, string> $names           the name indexed by locale
     * @param array<array-key, string> $simplifiedNames the simplified name indexed by locale
     */
    public function __construct(
        public readonly array $names,
        #[SerializedName('simplified_names')]
        public readonly array $simplifiedNames,
        #[SerializedName('forms_label')]
        public readonly ?string $formsLabel,
    ) {}
}
