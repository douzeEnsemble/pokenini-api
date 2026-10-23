<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\InvalidHomePageException;
use App\Repository\TrainerPreferenceRepository;

class TrainerPreferenceService
{
    public const string DEFAULT_HOME_PAGE = 'hub';

    private const array HOME_PAGES = [
        'hub' => true,
        'album' => true,
        'election' => true,
        'game' => true,
    ];

    public function __construct(
        private readonly TrainerPreferenceRepository $repository,
    ) {}

    public function getHomePage(string $trainerExternalId): string
    {
        return $this->repository->getHomePage($trainerExternalId) ?? self::DEFAULT_HOME_PAGE;
    }

    public function setHomePage(string $trainerExternalId, string $homePage): void
    {
        if (!isset(self::HOME_PAGES[$homePage])) {
            throw new InvalidHomePageException();
        }

        $this->repository->upsertHomePage($trainerExternalId, $homePage);
    }
}
