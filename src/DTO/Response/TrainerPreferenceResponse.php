<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class TrainerPreferenceResponse
{
    public function __construct(
        #[SerializedName('home_page')]
        public readonly string $homePage,
    ) {}
}
