<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220812131058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Add column without NO NULL constraint
        $this->addSql('ALTER TABLE catch_state ADD french_name VARCHAR(255) NULL');

        $catchStatesNames = [
            'No' => 'Non',
            'ToEvolve' => 'af. évoluer',
            'ToBreed' => 'af. reproduire',
            'ToTransfer' => 'à transférer',
            'Yes' => 'Oui',
        ];
        $this->addCatchStateFrenchNames($catchStatesNames);

        // Alter column without NO NULL constraint
        $this->addSql('ALTER TABLE catch_state ALTER french_name SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE catch_state DROP french_name');
    }

    private function addCatchStateFrenchNames(array $catchStatesNames): void
    {
        foreach ($catchStatesNames as $catchStateName => $catchStateFrenchName) {
            $this->addSql(
                "UPDATE catch_state SET french_name = :catchStateFrenchName WHERE name = :catchStateName",
                [
                    'catchStateFrenchName' => $catchStateFrenchName,
                    'catchStateName' => $catchStateName,
                ]
            );
        }
    }
}
