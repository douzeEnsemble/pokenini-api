<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * The name of the entity in every language, indexed by locale (["en" => "...", "fr" => "..."]).
 *
 * Adding a language does not change the schema.
 */
trait TranslatedNamesTrait
{
    /** @var array<string, string> */
    #[ORM\Column(type: Types::JSON, options: ['jsonb' => true, 'default' => '{}'])]
    public array $names = [];
}
