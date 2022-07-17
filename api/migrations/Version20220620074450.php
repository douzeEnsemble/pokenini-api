<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Uuid;

final class Version20220620074450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fill named data';
    }

    public function up(Schema $schema): void
    {
        $this->insertNamesIntoTable('catch_state', [
            'No',
            'ToEvolve',
            'ToBreed',
            'ToTransfer',
            'Yes'
        ]);

        $this->insertNamesIntoTable('variant_form', [
            'Gender',
            'Alternate',
            'Baby',
            'Battle',
            'Item',
            'Fusion',
            'Unobtainable',
        ]);

        $this->insertNamesIntoTable('regional_form', [
            'Alolan',
            'Galarian',
            'Hisuian',
        ]);

        $this->insertNamesIntoTable('special_form', [
            'Mega',
            'Gigantamax',
            'Totem',
            'Alpha',
            'Event',
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('TRUNCATE TABLE catch_state CASCADE');
        $this->addSql('TRUNCATE TABLE variant_form CASCADE');
        $this->addSql('TRUNCATE TABLE regional_form CASCADE');
        $this->addSql('TRUNCATE TABLE special_form CASCADE');
    }

    private function insertNamesIntoTable(string $tableName, array $names): void
    {
        if (empty($names)) {
            return;
        }

        $slugify = new Slugify();

        $sqlValues = [];
        $sqlParameters = [];
        $i = 0;
        foreach ($names as $name) {
            $sqlValues[] = ":id$i, :name$i, :slug$i, :order_number$i";
            $sqlParameters["id$i"] = Uuid::v4();
            $sqlParameters["name$i"] = $name;
            $sqlParameters["slug$i"] = $slugify->slugify($name, '');
            $sqlParameters["order_number$i"] = $i+1;

            $i++;
        }

        $sqlValuesStr = implode('), (', $sqlValues);

        $this->addSql("INSERT INTO $tableName (id, name, slug, order_number) VALUES ($sqlValuesStr)", $sqlParameters);
    }
}
