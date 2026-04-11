<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity]
#[UniqueConstraint(name: 'trainer_election_pokemon', columns: ['trainer_external_id', 'dex_id', 'election_slug', 'pokemon_id'])]
final class TrainerPokemonElo
{
    use BaseEntityTrait;

    #[ORM\Column]
    public int $elo = 0;

    #[ORM\Column]
    public int $viewCount = 0;

    #[ORM\Column]
    public int $winCount = 0;

    public function __construct(
        #[ORM\Column]
        private readonly string $trainerExternalId,
        #[ORM\ManyToOne(targetEntity: Pokemon::class)]
        #[ORM\JoinColumn(nullable: false)]
        private readonly Pokemon $pokemon,
        #[ORM\ManyToOne(targetEntity: Dex::class)]
        #[ORM\JoinColumn(nullable: false)]
        private readonly Dex $dex,
        #[ORM\Column]
        private readonly string $electionSlug = '',
    ) {}

    public function getTrainerExternalId(): string
    {
        return $this->trainerExternalId;
    }

    public function getPokemon(): Pokemon
    {
        return $this->pokemon;
    }

    public function getDex(): Dex
    {
        return $this->dex;
    }

    public function getElectionSlug(): string
    {
        return $this->electionSlug;
    }
}
