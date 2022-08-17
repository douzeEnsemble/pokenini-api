<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220817133517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix Home Pokémon Go selection rule';
    }

    public function up(Schema $schema): void
    {
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

    public function down(Schema $schema): void
    {
        $rule = <<<RULE
            (p.bankable or p.bankableish)
            and p.variantForm == null
            and p.specialForm == null
            and p.regionalForm == null
        RULE;
        $this->addSql("UPDATE dex SET selection_rule = :rule WHERE slug = 'homepokemongo'", ['rule' => $rule]);
    }
}
