<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220817083308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix Home Shiny selection rule';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ALTER selection_rule TYPE VARCHAR(1357)');

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
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ALTER selection_rule TYPE VARCHAR(1357)');

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
                    'Alcremie-Vanilla-Cream-Strawberry', 'Gigantamax Alcremie-Vanilla-Cream-Strawberry',
                    'Alcremie-Ruby-Cream-Berry', 'Gigantamax Alcremie-Ruby-Cream-Berry',
                    'Alcremie-Matcha-Cream-Love', 'Gigantamax Alcremie-Matcha-Cream-Love',
                    'Alcremie-Mint-Cream-Star', 'Gigantamax Alcremie-Mint-Cream-Star',
                    'Alcremie-Lemon-Cream-Clover', 'Gigantamax Alcremie-Lemon-Cream-Clover',
                    'Alcremie-Salted-Cream-Flower', 'Gigantamax Alcremie-Salted-Cream-Flower',
                    'Alcremie-Ruby-Swirl-Ribbon', 'Gigantamax Alcremie-Ruby-Swirl-Ribbon',
                    'Alcremie-Caramel-Swirl-Strawberry', 'Gigantamax Alcremie-Caramel-Swirl-Strawberry',
                    'Alcremie-Rainbow-Swirl-Berry', 'Gigantamax Alcremie-Rainbow-Swirl-Berry'
                ]
            )
        RULE;
        $this->addSql("UPDATE dex SET selection_rule = :rule WHERE slug = 'homeshiny'", ['rule' => $rule]);
    }
}
