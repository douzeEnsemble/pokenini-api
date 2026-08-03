<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\TrainerDex;
use App\Exception\DuplicateTrainerDexLinkException;
use App\Exception\SelfTrainerDexLinkException;
use App\Exception\TrainerDexNotFoundException;
use App\Repository\TrainerDexLinkRepository;
use App\Repository\TrainerDexRepository;
use Symfony\Component\Uid\Uuid;

class TrainerDexLinkService
{
    public function __construct(
        private readonly TrainerDexLinkRepository $repository,
        private readonly TrainerDexRepository $trainerDexRepository,
    ) {}

    /**
     * @return list<array{id: string, pair_id: ?string, direction: string, target_trainer_dex_id: string, target_dex_slug: string, target_name: string, target_french_name: string}>
     */
    public function listForDex(string $trainerExternalId, string $dexSlug): array
    {
        return $this->repository->getForDex($trainerExternalId, $dexSlug);
    }

    public function create(
        string $trainerExternalId,
        string $sourceDexSlug,
        string $targetDexSlug,
        bool $bidirectional,
    ): void {
        if ($sourceDexSlug === $targetDexSlug) {
            throw new SelfTrainerDexLinkException();
        }

        $sourceId = (string) $this->findTrainerDex($trainerExternalId, $sourceDexSlug)->getIdentifier();
        $targetId = (string) $this->findTrainerDex($trainerExternalId, $targetDexSlug)->getIdentifier();

        if ($this->repository->exists($sourceId, $targetId)
            || ($bidirectional && $this->repository->exists($targetId, $sourceId))
        ) {
            throw new DuplicateTrainerDexLinkException();
        }

        $pairId = $bidirectional ? (string) Uuid::v4() : null;

        $this->repository->insert($trainerExternalId, $sourceId, $targetId, $pairId);

        if ($bidirectional) {
            $this->repository->insert($trainerExternalId, $targetId, $sourceId, $pairId);
        }
    }

    public function delete(string $trainerExternalId, string $linkId): void
    {
        $this->repository->deleteByIdOrPairId($trainerExternalId, $linkId);
    }

    private function findTrainerDex(string $trainerExternalId, string $dexSlug): TrainerDex
    {
        $trainerDex = $this->trainerDexRepository->findOneBy([
            'trainerExternalId' => $trainerExternalId,
            'slug' => $dexSlug,
        ]);

        if (null === $trainerDex) {
            throw new TrainerDexNotFoundException();
        }

        return $trainerDex;
    }
}
