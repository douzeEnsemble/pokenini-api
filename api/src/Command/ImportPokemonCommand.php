<?php

namespace App\Command;

use App\Exception\InvalidFilePathDataException;
use App\Exception\InvalidFileDataException;
use App\Exception\NoDataPokemonException;
use App\Repository\PokemonRepository;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(name: 'app:import:pokemon')]
class ImportPokemonCommand extends AbstractImportFileCommand
{
    protected static $defaultName = 'app:import:pokemon';

    private const CHUNK_SIZE = 10;

    public function __construct(
        private PokemonRepository $pokemonRepository,
        protected EntityManagerInterface $entityManager,
    ) {
        parent::__construct($this->entityManager);
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setHelp('This command allows you to import pokemon list from a csv')
        ;
    }

    protected function processRecords(\Iterator $records, InputInterface $input, OutputInterface $output): void
    {
        $pokemons = $this->getPokemonsFromRecords($records);

        $this->pokemonRepository->removeAll();

        $pokemonsChunks = array_chunk($pokemons, self::CHUNK_SIZE);
        foreach ($pokemonsChunks as $chunk) {
            $this->upsertPokemons($chunk);
        }

        $nbPokemons = count($pokemons);

        $output->writeln("<info>$nbPokemons pokemons created/updated</info>");
    }

    /**
     * @return string[]
     */
    protected function getExpectedHeader(): array
    {
        return [
            'Bankable',
            'Breeedable Form',
            'Origin',
            'Games First Appears On',
            'Form variant',
            'Regional form',
            'Special form',
            'Family',
            'Family order',
            'Evolution',
            'Pokémon',
            'Dex',
            'Sprites',
            'Shiny Sprites',
            'Type 1',
            'Type 1 ico',
            'Type 2',
            'Type 2 ico',
            'Steps',
            'Egg Group',
            'Egg Group 2',
            'Male',
            'Female',
            'Ability 1',
            'Ability 2',
            'Hidden Ability',
            'Abilities',
            'All Moves',
            'Move Type',
            'Natures',
            'Increases',
            'Decreases',
            'Icon',
            'Bulbapedia Name',
            'Bankable-ish',
            'Slug',
        ];
    }

    /**
     * @param \Iterator|string[][] $records
     *
     * @return string[][]|bool[][]
     */
    private function getPokemonsFromRecords(\Iterator $records): array
    {
        $pokemons = [];
        foreach ($records as $record) {
            $pokemons[] = $this->transformRecord($record);
        }

        return $pokemons;
    }

    /**
     * @param string[] $record
     *
     * @return string[]|bool[]
     */
    private function transformRecord(array $record): array
    {
        /** @var bool $isBankable */
        $isBankable = filter_var($record['Bankable'], FILTER_VALIDATE_BOOLEAN);
        /** @var bool $isBankableish */
        $isBankableish = filter_var($record['Bankable-ish'], FILTER_VALIDATE_BOOLEAN);

        return [
            'name' => $record['Pokémon'],
            'nationalDexNumber' => $record['Dex'],
            'family' => $record['Family'],
            'familyOrder' => $record['Family order'],
            'bankable' => $isBankable,
            'bankableish' => $isBankableish,
            'originalGameBundle' => $record['Games First Appears On'],
            'variantForm' => $record['Form variant'],
            'regionalForm' => $record['Regional form'],
            'specialForm' => $record['Special form'],
            'iconName' => $record['Bulbapedia Name'],
            'slug' => $record['Slug'],
        ];
    }

    /**
     * @param string[][]|bool[][] $pokemons
     */
    private function upsertPokemons(array $pokemons): void
    {
        if (empty($pokemons)) {
            return;
        }

        $sqlValues = [];
        $sqlParameters = [];
        $index = 0;
        foreach ($pokemons as $pokemon) {
            $sqlValues[] = ":id$index"
                . ", :name$index"
                . ", :national_dex_number$index"
                . ", (SELECT id FROM pokemon WHERE name = :family$index)"
                . ", :familyOrder$index"
                . ", :bankable$index"
                . ", :bankableish$index"
                . ", (SELECT id FROM game_bundle WHERE name = :original_game_bundle$index)"
                . ", (SELECT id FROM variant_form WHERE name = :variant_form$index)"
                . ", (SELECT id FROM regional_form WHERE name = :regional_form$index)"
                . ", (SELECT id FROM special_form WHERE name = :special_form$index)"
                . ", :iconName$index"
                . ", :slug$index"
            ;

            $sqlParameters["id$index"] = Uuid::v4();
            $sqlParameters["name$index"] = $pokemon['name'];
            $sqlParameters["national_dex_number$index"] = $pokemon['nationalDexNumber'];
            $sqlParameters["family$index"] = $pokemon['family'];
            $sqlParameters["familyOrder$index"] = $pokemon['familyOrder'];
            $sqlParameters["bankable$index"] = $pokemon['bankable'] ? 'TRUE' : 'FALSE';
            $sqlParameters["bankableish$index"] = $pokemon['bankableish'] ? 'TRUE' : 'FALSE';
            $sqlParameters["original_game_bundle$index"] = $pokemon['originalGameBundle'];
            $sqlParameters["variant_form$index"] = $pokemon['variantForm'];
            $sqlParameters["regional_form$index"] = $pokemon['regionalForm'];
            $sqlParameters["special_form$index"] = $pokemon['specialForm'];
            $sqlParameters["iconName$index"] = $pokemon['iconName'];
            $sqlParameters["slug$index"] = $pokemon['slug'];

            $index++;
        }

        $sqlValuesStr = implode('), (', $sqlValues);

        $sql = <<<SQL
        INSERT INTO pokemon (
            id,
            name,
            national_dex_number,
            family_id,
            family_order,
            bankable,
            bankableish,
            original_game_bundle_id,
            variant_form_id,
            regional_form_id,
            special_form_id,
            icon_name,
            slug
        )
        VALUES ($sqlValuesStr)
        ON CONFLICT (name)
        DO
        UPDATE
        SET national_dex_number = excluded.national_dex_number,
            family_id = excluded.family_id,
            family_order = excluded.family_order,
            bankable = excluded.bankable,
            bankableish = excluded.bankableish,
            original_game_bundle_id = excluded.original_game_bundle_id,
            variant_form_id = excluded.variant_form_id,
            regional_form_id = excluded.regional_form_id,
            special_form_id = excluded.special_form_id,
            icon_name = excluded.icon_name,
            slug = excluded.slug,
            deleted_at = NULL
SQL;

        try {
            $this->entityManager->getConnection()->executeQuery($sql, $sqlParameters);
        } catch (\Exception $e) {
            var_dump($sqlParameters);

            throw $e;
        }
    }
}
