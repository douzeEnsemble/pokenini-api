<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Uuid;

final class Version20220818091235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Category form on Pokémon';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE category_form (id UUID NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, order_number INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D91387085E237E06 ON category_form (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D9138708989D9B62 ON category_form (slug)');
        $this->addSql('COMMENT ON COLUMN category_form.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE pokemon ADD category_form_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN pokemon.category_form_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F3982EDB03 FOREIGN KEY (category_form_id) REFERENCES category_form (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_62DC90F3982EDB03 ON pokemon (category_form_id)');

        $this->insertNamesIntoCategoryForm([
            'Starter',
            'Legendary',
            'Mythical',
            'Ultra Beast',
        ]);

        $rule = <<<RULE
            (p.bankable or p.bankableish)
                and p.variantForm == null
                and p.specialForm == null
                and p.regionalForm == null
                and (p.categoryForm === null
                    or p.categoryForm?.name in ['Starter', 'Legendary', 'Ultra Beast']
                )
        RULE;
        $this->addSql("UPDATE dex SET selection_rule = :rule WHERE slug = 'homepokemongo'", ['rule' => $rule]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon DROP CONSTRAINT FK_62DC90F3982EDB03');
        $this->addSql('DROP TABLE category_form');
        $this->addSql('DROP INDEX IDX_62DC90F3982EDB03');
        $this->addSql('ALTER TABLE pokemon DROP category_form_id');

        $rule = <<<RULE
            (p.bankable or p.bankableish)
                and p.variantForm == null
                and p.specialForm == null
                and p.regionalForm == null
            and p.name not in [
                'Phione',
                'Manaphy',
                'Darkrai',
                'Shaymin',
                'Arceus',
                'Victini',
                'Keldeo',
                'Meloetta',
                'Genesect',
                'Diancie',
                'Hoopa',
                'Volcanion',
            ]
        RULE;
        $this->addSql("UPDATE dex SET selection_rule = :rule WHERE slug = 'homepokemongo'", ['rule' => $rule]);
    }

    private function insertNamesIntoCategoryForm(array $names): void
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

        $this->addSql("INSERT INTO category_form (id, name, slug, order_number) VALUES ($sqlValuesStr)", $sqlParameters);
    }
}
