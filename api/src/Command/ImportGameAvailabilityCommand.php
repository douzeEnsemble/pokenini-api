<?php

namespace App\Command;

use App\Exception\InvalidFilePathDataException;
use App\Exception\InvalidFileDataException;
use App\Exception\NoDataPokemonException;
use App\Repository\GameAvailabilityRepository;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(name: 'app:import:game_availability')]
class ImportGameAvailabilityCommand extends AbstractImportFileCommand
{
    protected static $defaultName = 'app:import:game_availability';

    private const CHUNK_SIZE = 38;

    public function __construct(
        private GameAvailabilityRepository $gameAvailabilityRepository,
        protected EntityManagerInterface   $entityManager,

    ) {
        parent::__construct($this->entityManager);
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setHelp('This command allows you to import availability list from a csv')
        ;
    }

    protected function processRecords(\Iterator $records, InputInterface $input, OutputInterface $output): void
    {
        $availabilities = $this->getGameAvailabilitiesFromRecords($records);

        $this->gameAvailabilityRepository->removeAll();

        $availabilitiesChunks = array_chunk($availabilities, self::CHUNK_SIZE);
        foreach ($availabilitiesChunks as $chunk) {
            $this->insertGameAvailabilities($chunk);
        }

        $nbAvailabilities = count($availabilities);

        $output->writeln("<info>$nbAvailabilities games' availabilities created</info>");
    }

    protected function getExpectedHeader(): array
    {
        return [
            '#',
            'Name',
            'Red',
            'Green',
            'Blue',
            'Yellow',
            'Gold',
            'Silver',
            'Crystal',
            'Ruby',
            'Sapphire',
            'Red Fire',
            'Leaf Green',
            'Emerald',
            'Diamond',
            'Pearl',
            'Platinium',
            'Heart Gold',
            'Soul Silver',
            'Black',
            'White',
            'Black 2',
            'White 2',
            'X',
            'Y',
            'Omega Ruby',
            'Alpha Sapphire',
            'Sun',
            'Moon',
            'Ultra Sun',
            'Ultra Moon',
            'Let\'s Go Pikachu',
            'Let\'s Go Eevee',
            'Sword',
            'Shield',
            'Brillant Diamond',
            'Shining Pearl',
            'Legend Arceus',
        ];
    }

    private function getGameAvailabilitiesFromRecords(\Iterator $records): array
    {
        $gameAvailabilities = [];
        foreach ($records as $record) {
            $this->transformRecord($gameAvailabilities, $record);
        }

        return $gameAvailabilities;
    }

    /**
     * @param mixed[] $gameAvailabilities
     * @param mixed[] $record
     *
     * @return mixed[]
     */
    private function transformRecord(array &$gameAvailabilities, array $record): array
    {
        unset($record['#']);

        $name = $record['Name'];
        unset($record['Name']);

        foreach ($record as $game => $availability) {
            $gameAvailabilities[] = [
                'pokemon' => $name,
                'game' => $game,
                'availability' => $availability,
            ];
        }

        return $gameAvailabilities;
    }

    private function insertGameAvailabilities(array $gameAvailabilities)
    {
        if (empty($gameAvailabilities)) {
            return;
        }

        $sqlValues = [];
        $sqlParameters = [];
        $i = 0;
        foreach ($gameAvailabilities as $gameAvailability) {
            $sqlValues[] = ":id$i"
                . ", (SELECT id FROM pokemon WHERE name = :pokemon$i)"
                . ", (SELECT id FROM game WHERE name = :game$i)"
                . ", :availability$i"
            ;

            $sqlParameters["id$i"] = Uuid::v4();
            $sqlParameters["pokemon$i"] = $gameAvailability['pokemon'];
            $sqlParameters["game$i"] = $gameAvailability['game'];
            $sqlParameters["availability$i"] = $gameAvailability['availability'];

            $i++;
        }

        $sqlValuesStr = implode('), (', $sqlValues);

        $sql = <<<SQL
        INSERT INTO game_availability (
            id,
            pokemon_id,
            game_id,
            availability
        )
        VALUES ($sqlValuesStr)
SQL;

        $this->entityManager->getConnection()->executeQuery($sql, $sqlParameters);
    }
}
