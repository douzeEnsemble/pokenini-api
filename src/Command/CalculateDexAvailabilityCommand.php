<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Entity\Pokemon;
use App\Repository\DexAvailabilityRepository;
use App\Repository\DexRepository;
use App\Repository\PokemonRepository;
use App\Service\GameBundleAvailabilityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Contracts\Cache\CacheInterface;

#[AsCommand(name: 'app:calculate:dex_availability')]
class CalculateDexAvailabilityCommand extends Command
{
    protected static $defaultName = 'app:calculate:dex_availability';

    public function __construct(
        private readonly DexAvailabilityRepository $dexAvailabilityRepository,
        private readonly GameBundleAvailabilityService $gameBundleAvailabilityService,
        private readonly DexRepository $dexRepository,
        private readonly PokemonRepository $pokemonRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheInterface $cache,
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setHelp("This command allows you to update dex availabilities")
        ;
    }

    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->cache->clear();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dexAvailabilityRepository->removeAll();

        $dexQuery = $this->dexRepository->getQueryAll();
        $dexCount = $this->dexRepository->countAll();

        $pokemonCount = $this->pokemonRepository->countAll();

        ProgressBar::setFormatDefinition('custom', '%message:36s% - %current:5s%/%max:5s% [%bar%] %percent:3s%%');
        $expressionLanguage = new ExpressionLanguage();

        $progressMain = $this->getProgressBar($output);
        $progressMain->setMessage('Main');
        $progressMain->setFormat('custom');
        $progressMain->start($dexCount * $pokemonCount);

        $nbDexAvailabilities = 0;
        /** @var Dex $dex */
        foreach ($dexQuery->toIterable() as $dex) {
            $progressDex = $this->getProgressBar($output);
            $progressDex->setMessage("Dex {$dex->name}");
            $progressDex->setFormat('custom');
            $progressDex->start($pokemonCount);

            $pokemonQuery = $this->pokemonRepository->getQueryAll();

            /** @var Pokemon $pokemon */
            foreach ($pokemonQuery->toIterable() as $pokemon) {
                $isGettable = $expressionLanguage->evaluate(
                    $dex->selectionRule,
                    [
                        'p' => $pokemon,
                        'ba' => $this->gameBundleAvailabilityService->getFromPokemon($pokemon),
                    ]
                );

                if (!$isGettable) {
                    $progressMain->advance();
                    $progressDex->advance();

                    continue;
                }

                $dexAvailability = DexAvailability::create($pokemon, $dex);

                $this->entityManager->persist($dexAvailability);

                $nbDexAvailabilities++;

                $progressMain->advance();
                $progressDex->advance();
            }

            $progressDex->finish();

            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        $progressMain->finish();

        $output->writeln("<info>$nbDexAvailabilities dex' availabilities calculated</info>");

        return Command::SUCCESS;
    }

    private function getProgressBar(OutputInterface $output): ProgressBar
    {
        if (method_exists($output, 'section')) {
            return new ProgressBar($output->section());
        }

        return new ProgressBar($output);
    }
}
