<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class DexDebugResponse
{
    /**
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        public readonly ?string $identifier,
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        #[SerializedName('order_number')]
        public readonly int $orderNumber,
        #[SerializedName('selection_rule')]
        public readonly string $selectionRule,
        public readonly DexDebugFlagsResponse $flags,
        #[SerializedName('display_template')]
        public readonly string $displayTemplate,
        public readonly ?RegionResponse $region,
        public readonly string $description,
        #[SerializedName('french_description')]
        public readonly string $frenchDescription,
        #[SerializedName('last_changed_at')]
        public readonly string $lastChangedAt,
        #[SerializedName('election_order_number')]
        public readonly int $electionOrderNumber,
        #[SerializedName('deleted_at')]
        public readonly ?string $deletedAt,
    ) {}
}
