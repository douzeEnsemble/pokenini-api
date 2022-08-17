<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220817083501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Alpha dex';
    }

    public function up(Schema $schema): void
    {
        $rule = <<<RULE
            (p.bankable or p.bankableish)
            and p.specialForm?.name === 'Alpha'
        RULE;

        $sql = <<<SQL
            INSERT INTO dex (id, name, french_name, selection_rule, slug, is_shiny, is_private, order_number)
            VALUES (gen_random_uuid(), :name, :frenchName, :selectionRule, :slug, :isShiny, :isPrivate, (SELECT MAX(order_number) + 1 FROM dex))
            SQL;

        $this->addSql($sql, [
            'name' => 'Alpha',
            'frenchName' => 'Baron',
            'selectionRule' => $rule,
            'slug' => 'alpha',
            'isShiny' => 'false',
            'isPrivate' => 'false',
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM dex WHERE slug = 'alpha'");
    }
}
