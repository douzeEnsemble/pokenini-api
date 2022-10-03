<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220907091717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new dex and update HomeShiny';
    }

    public function up(Schema $schema): void
    {
        $rule = <<<RULE
            (p.bankable or p.bankableish)
            and (
                p.variantForm === null
                or p.variantForm?.name in ['Baby', 'Alternate', 'Gender', 'Therian']
            )
            and (
                p.specialForm === null
                or p.specialForm?.name === 'Gigantamax'
            )
            and (
                p.primeName !== 'Alcremie'
                or p.name in [
                    'Alcremie-Vanilla-Cream-Strawberry',
                    'Alcremie-Ruby-Cream-Berry',
                    'Alcremie-Matcha-Cream-Love',
                    'Alcremie-Mint-Cream-Star',
                    'Alcremie-Lemon-Cream-Clover',
                    'Alcremie-Salted-Cream-Flower',
                    'Alcremie-Ruby-Swirl-Ribbon',
                    'Alcremie-Caramel-Swirl-Strawberry',
                    'Alcremie-Rainbow-Swirl-Berry',
                    'Gigantamax Alcremie-Rainbow-Swirl-Ribbon'
                ]
            )
        RULE;

        $this->addSql("UPDATE dex SET is_private = false WHERE slug = 'swordshield'");

        $sql = <<<SQL
            INSERT INTO dex (id, name, french_name, selection_rule, slug, order_number, is_shiny, is_private, is_display_form)
            VALUES (gen_random_uuid(), :name, :french_name, :selectionRule, :slug, (SELECT MAX(order_number) + 1 FROM dex), false, false, true)
            SQL;

        $this->addSql($sql, [
            'name' => 'Mega',
            'french_name' => 'Méga',
            'selectionRule' => <<< RULE
                (p.bankable or p.bankableish)
                and p.specialForm?.name === 'Mega'
                RULE,
            'slug' => 'mega',
        ]);
    }

    public function down(Schema $schema): void
    {
        $rule = <<<RULE
            (p.bankable or p.bankableish)
            and (
                p.variantForm === null
                or p.variantForm?.name in ['Baby', 'Alternate', 'Gender', 'Therian']
            )
            and (
                p.specialForm === null
                or p.specialForm?.name === 'Gigantamax'
            )
            and (
                p.primeName !== 'Alcremie'
                or p.name in [
                    'Alcremie-Vanilla-Cream-Strawberry',
                    'Alcremie-Ruby-Cream-Berry',
                    'Alcremie-Matcha-Cream-Love',
                    'Alcremie-Mint-Cream-Star',
                    'Alcremie-Lemon-Cream-Clover',
                    'Alcremie-Salted-Cream-Flower',
                    'Alcremie-Ruby-Swirl-Ribbon',
                    'Alcremie-Caramel-Swirl-Strawberry',
                    'Alcremie-Rainbow-Swirl-Berry',
                    'Gigantamax Alcremie-Vanilla-Cream-Strawberry'
                ]
            )
        RULE;
        $this->addSql("UPDATE dex SET selection_rule = :rule WHERE slug = 'homeshiny'", ['rule' => $rule]);

        $this->addSql("UPDATE dex SET is_private = true WHERE slug = 'swordshield'");

        $this->addSql("DELETE FROM dex WHERE slug = 'mega'");
    }
}
