<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Entity\Pokemon;
use App\Repository\Trait\FiltersTrait;
use App\Service\SqlFileLoader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pokemon>
 */
class PokemonsRepository extends ServiceEntityRepository
{
    use FiltersTrait;

    public function __construct(ManagerRegistry $registry, private readonly SqlFileLoader $sqlFileLoader)
    {
        parent::__construct($registry, Pokemon::class);
    }

    /**
     * @return Query<Pokemon>
     *
     * @psalm-suppress TooManyTemplateParams
     */
    public function getQueryAll(): Query
    {
        $queryBuilder = $this->createQueryBuilder('p');

        $queryBuilder->where($queryBuilder->expr()->isNull('p.deletedAt'));
        $queryBuilder->orderBy('p.nationalDexNumber, p.familyOrder');

        return $queryBuilder->getQuery();
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getNToPick(
        string $dexSlug,
        int $count,
        string $trainerExternalId,
        string $electionSlug,
        AlbumFilters $filters,
        int $defaultElo,
    ): array {
        $sql = $this->sqlFileLoader->load('pokemons-get_n_to_pick.sql');
        $sql = $this->replaceFilters($sql, $filters);

        $params = array_merge(
            [
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
                'election_slug' => $electionSlug,
                'count' => $count,
                'default_elo' => $defaultElo,
            ],
            $this->getFiltersParameters($filters),
        );

        $types = array_merge(
            [
                'trainer_external_id' => ParameterType::STRING,
                'election_slug' => ParameterType::STRING,
                'dex_slug' => ParameterType::STRING,
                'count' => ParameterType::INTEGER,
                'default_elo' => ParameterType::INTEGER,
            ],
            $this->getFiltersTypes(),
        );

        /** @var array<array<string, mixed>> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            $params,
            $types,
        );
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getNToVote(
        string $dexSlug,
        int $count,
        string $trainerExternalId,
        string $electionSlug,
        AlbumFilters $filters,
        int $defaultElo,
    ): array {
        $sql = $this->sqlFileLoader->load('pokemons-get_n_to_vote.sql');
        $sql = $this->replaceFilters($sql, $filters);

        $params = array_merge(
            [
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
                'election_slug' => $electionSlug,
                'count' => $count,
                'default_elo' => $defaultElo,
            ],
            $this->getFiltersParameters($filters),
        );

        $types = array_merge(
            [
                'trainer_external_id' => ParameterType::STRING,
                'election_slug' => ParameterType::STRING,
                'dex_slug' => ParameterType::STRING,
                'count' => ParameterType::INTEGER,
                'default_elo' => ParameterType::INTEGER,
            ],
            $this->getFiltersTypes(),
        );

        /** @var array<array<string, mixed>> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            $params,
            $types,
        );
    }

    private function replaceFilters(string $sql, AlbumFilters $filters): string
    {
        return str_replace('-- {album_filters}', $this->getFiltersQuery($filters), $sql);
    }
}
