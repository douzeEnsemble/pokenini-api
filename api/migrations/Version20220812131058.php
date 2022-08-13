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
            'no' => [
                'en' => 'No',
                'fr' => 'Non',
            ],
            'toevolve' => [
                'en' => 'To evolve',
                'fr' => 'af. évoluer',
            ],
            'tobreed' => [
                'en' => 'To breed',
                'fr' => 'af. reproduire',
            ],
            'totransfer' => [
                'en' => 'To transfer',
                'fr' => 'à transférer',
            ],
            'yes' => [
                'en' => 'Yes',
                'fr' => 'Oui',
            ],
        ];
        $this->updateCatchStateNames($catchStatesNames);

        // Alter column without NO NULL constraint
        $this->addSql('ALTER TABLE catch_state ALTER french_name SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE catch_state DROP french_name');
    }

    private function updateCatchStateNames(array $catchStatesNames): void
    {
        foreach ($catchStatesNames as $catchStateSlug => $catchStateNames) {
            $sql = <<< SQL
            UPDATE  catch_state
            SET     french_name = :catchStateFrenchName,
                    name = :catchStateName
            WHERE   slug = :catchStateSlug
            SQL;

            $this->addSql(
                $sql,
                [
                    'catchStateSlug' => $catchStateSlug,
                    'catchStateFrenchName' => $catchStateNames['fr'],
                    'catchStateName' => $catchStateNames['en'],
                ]
            );
        }
    }
}
