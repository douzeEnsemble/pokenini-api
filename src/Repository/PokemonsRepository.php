<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Entity\Pokemon;
use App\Repository\Trait\FiltersTrait;
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

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pokemon::class);
    }

    public function removeAll(): void
    {
        /** @psalm-suppress QueryBuilderSetParameter */
        $queryBuilder = $this->createQueryBuilder('p')
            ->update()
            ->set('p.deletedAt', ':now')
            ->setParameter('now', new \DateTimeImmutable())
        ;

        $queryBuilder->getQuery()->execute();
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

    public function countAll(): int
    {
        $queryBuilder = $this->createQueryBuilder('p');

        $queryBuilder->select($queryBuilder->expr()->count('p'));

        /** @var int */
        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return string[][]
     */
    public function getNToPick(
        string $dexSlug,
        int $count,
        string $trainerExternalId,
        string $electionSlug,
        AlbumFilters $filters,
        int $defaultElo,
    ): array {
        $sql = $this->getNToPickSQL();
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

        /** @var string[][] */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            $params,
            $types,
        );
    }

    /**
     * @return string[][]
     */
    public function getNToVote(
        string $dexSlug,
        int $count,
        string $trainerExternalId,
        string $electionSlug,
        AlbumFilters $filters,
        int $defaultElo,
    ): array {
        $sql = $this->getNToVoteSQL();
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

        /** @var string[][] */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            $params,
            $types,
        );
    }

    private function getNToPickSQL(): string
    {
        $sql = file_get_contents(dirname(__DIR__).'/../resources/sql/pokemons-get_n_to_pick.sql');

        if (false === $sql) {
            // This condition is here form safety reason
            // It can never happen
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Failed to read SQL file "pokemons-get_n_to_pick.sql"');
            // @codeCoverageIgnoreEnd
        }

        return $sql;
    }

    private function getNToVoteSQL(): string
    {
        $sql = file_get_contents(dirname(__DIR__).'/../resources/sql/pokemons-get_n_to_vote.sql');

        if (false === $sql) {
            // This condition is here form safety reason
            // It can never happen
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Failed to read SQL file "pokemons-get_n_to_vote.sql"');
            // @codeCoverageIgnoreEnd
        }

        return $sql;
    }

    private function replaceFilters(string $sql, AlbumFilters $filters): string
    {
        return str_replace('-- {album_filters}', $this->getFiltersQuery($filters), $sql);
    }
}
