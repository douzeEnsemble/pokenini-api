<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220818121910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix rule and dex is diplay form';
    }

    public function up(Schema $schema): void
    {
        $rule = <<<RULE
            (p.bankable or p.bankableish)
                and p.variantForm === null
                and p.specialForm === null
                and p.regionalForm === null
                and (p.categoryForm === null
                    or p.categoryForm?.name in ['Starter', 'Legendary', 'Ultra Beast']
                )
        RULE;
        $this->addSql("UPDATE dex SET selection_rule = :rule WHERE slug = 'homepokemongo'", ['rule' => $rule]);

        $this->addSql("UPDATE dex SET is_display_form = false WHERE slug = 'homepokemongo'");
    }

    public function down(Schema $schema): void
    {

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

        $this->addSql("UPDATE dex SET is_display_form = true WHERE slug = 'homepokemongo'");
    }
}
