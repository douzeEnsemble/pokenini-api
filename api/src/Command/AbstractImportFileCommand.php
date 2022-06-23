<?php

namespace App\Command;

use App\Exception\InvalidFilePathDataException;
use App\Exception\InvalidFileDataException;
use App\Exception\NoDataPokemonException;
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

abstract class AbstractImportFileCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $entityManager,

    ) {
        parent::__construct(self::$defaultName);
    }

    abstract protected function processRecords(\Iterator $records, InputInterface $input, OutputInterface $output): void;
    abstract protected function getExpectedHeader(): array;

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Data csv file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('file');

        try {
            $this->checkFilePath($filePath);

            $records = $this->getRecordsFromFile($filePath);
        } catch (InvalidFilePathDataException|InvalidFileDataException $e) {
            $output->write("<error>{$e->getMessage()}</error>");

            return Command::INVALID;
        } catch (NoDataPokemonException $e) {
            $output->write("<error>{$e->getMessage()}</error>");

            return Command::SUCCESS;
        }

        $this->processRecords($records, $input, $output);

        return Command::SUCCESS;
    }

    protected function checkFilePath(string $filePath): void
    {
        if (! is_file($filePath)) {
            throw new InvalidFilePathDataException(
                'File not found'
            );
        }

        $detector = new FinfoMimeTypeDetector();

        $typeMime = $detector->detectMimeTypeFromFile($filePath);

        if ('text/csv' !== $typeMime && 'text/plain' !== $typeMime) {
            throw new InvalidFilePathDataException(
                "File is not a valid csv ($typeMime isn't accepted)"
            );
        }
    }

    protected function getRecordsFromFile(mixed $filePath): \Iterator
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);
        $csv->skipEmptyRecords();

        $header = $csv->getHeader();

        if ($header !== $this->getExpectedHeader()) {
            throw new InvalidFileDataException('This is not a valid data csv file');
        }

        if (!$csv->count()) {
            throw new NoDataPokemonException('No data to import');
        }

        return $csv->getRecords();
    }
}
