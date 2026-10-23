<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity]
#[UniqueConstraint(name: 'uniq_trainer_preference_trainer', columns: ['trainer_external_id'])]
final class TrainerPreference
{
    use BaseEntityTrait;

    #[ORM\Column]
    public string $trainerExternalId = '';

    #[ORM\Column(length: 16)]
    public string $homePage = 'hub';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public \DateTimeImmutable $updatedAt;
}
