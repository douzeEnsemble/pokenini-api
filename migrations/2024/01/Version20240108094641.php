<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240108094641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE action_log (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, done_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, report_data VARCHAR(255) DEFAULT NULL, error_trace TEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN action_log.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE catch_state (id UUID NOT NULL, name VARCHAR(255) NOT NULL, french_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, order_number INT NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, color VARCHAR(255) DEFAULT \'\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4F8F865C5E237E06 ON catch_state (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4F8F865C989D9B62 ON catch_state (slug)');
        $this->addSql('COMMENT ON COLUMN catch_state.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE category_form (id UUID NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, order_number INT NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D91387085E237E06 ON category_form (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D9138708989D9B62 ON category_form (slug)');
        $this->addSql('COMMENT ON COLUMN category_form.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE dex (id UUID NOT NULL, region_id UUID DEFAULT NULL, selection_rule VARCHAR(13570) NOT NULL, is_shiny BOOLEAN NOT NULL, is_private BOOLEAN NOT NULL, is_display_form BOOLEAN NOT NULL, display_template VARCHAR(255) DEFAULT \'box\' NOT NULL, description VARCHAR(655) NOT NULL, french_description VARCHAR(655) NOT NULL, is_released BOOLEAN NOT NULL, last_changed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, french_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, order_number INT NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F6CBDC025E237E06 ON dex (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F6CBDC02989D9B62 ON dex (slug)');
        $this->addSql('CREATE INDEX IDX_F6CBDC0298260155 ON dex (region_id)');
        $this->addSql('COMMENT ON COLUMN dex.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN dex.region_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE dex_availability (id UUID NOT NULL, pokemon_id UUID NOT NULL, dex_id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C3DC5ADA2FE71C3E ON dex_availability (pokemon_id)');
        $this->addSql('CREATE INDEX IDX_C3DC5ADA44FE8083 ON dex_availability (dex_id)');
        $this->addSql('COMMENT ON COLUMN dex_availability.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN dex_availability.pokemon_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN dex_availability.dex_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE game (id UUID NOT NULL, bundle_id UUID NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, order_number INT NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_232B318C5E237E06 ON game (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_232B318C989D9B62 ON game (slug)');
        $this->addSql('CREATE INDEX IDX_232B318CF1FAD9D3 ON game (bundle_id)');
        $this->addSql('COMMENT ON COLUMN game.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN game.bundle_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE game_availability (id UUID NOT NULL, game_id UUID NOT NULL, pokemon_name VARCHAR(255) NOT NULL, availability VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D16796D1E48FD905 ON game_availability (game_id)');
        $this->addSql('COMMENT ON COLUMN game_availability.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN game_availability.game_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE game_bundle (id UUID NOT NULL, generation_id UUID NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, order_number INT NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D84E9F355E237E06 ON game_bundle (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D84E9F35989D9B62 ON game_bundle (slug)');
        $this->addSql('CREATE INDEX IDX_D84E9F35553A6EC4 ON game_bundle (generation_id)');
        $this->addSql('COMMENT ON COLUMN game_bundle.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN game_bundle.generation_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE game_bundle_availability (id UUID NOT NULL, pokemon_id UUID NOT NULL, bundle_id UUID NOT NULL, is_available BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A9097E132FE71C3E ON game_bundle_availability (pokemon_id)');
        $this->addSql('CREATE INDEX IDX_A9097E13F1FAD9D3 ON game_bundle_availability (bundle_id)');
        $this->addSql('COMMENT ON COLUMN game_bundle_availability.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN game_bundle_availability.pokemon_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN game_bundle_availability.bundle_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE game_bundle_shiny_availability (id UUID NOT NULL, pokemon_id UUID NOT NULL, bundle_id UUID NOT NULL, is_available BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_923E348F2FE71C3E ON game_bundle_shiny_availability (pokemon_id)');
        $this->addSql('CREATE INDEX IDX_923E348FF1FAD9D3 ON game_bundle_shiny_availability (bundle_id)');
        $this->addSql('COMMENT ON COLUMN game_bundle_shiny_availability.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN game_bundle_shiny_availability.pokemon_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN game_bundle_shiny_availability.bundle_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE game_generation (id UUID NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3DFAABD65E237E06 ON game_generation (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3DFAABD6989D9B62 ON game_generation (slug)');
        $this->addSql('COMMENT ON COLUMN game_generation.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE game_shiny_availability (id UUID NOT NULL, game_id UUID NOT NULL, pokemon_name VARCHAR(255) NOT NULL, availability VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_57A1BEF6E48FD905 ON game_shiny_availability (game_id)');
        $this->addSql('COMMENT ON COLUMN game_shiny_availability.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN game_shiny_availability.game_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE pokedex (id UUID NOT NULL, pokemon_id UUID NOT NULL, trainer_dex_id UUID DEFAULT NULL, catch_state_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6336F6A72FE71C3E ON pokedex (pokemon_id)');
        $this->addSql('CREATE INDEX IDX_6336F6A777B0DA37 ON pokedex (trainer_dex_id)');
        $this->addSql('CREATE INDEX IDX_6336F6A71339B3D7 ON pokedex (catch_state_id)');
        $this->addSql('CREATE UNIQUE INDEX pokemon_dex_trainer ON pokedex (pokemon_id, trainer_dex_id)');
        $this->addSql('COMMENT ON COLUMN pokedex.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN pokedex.pokemon_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN pokedex.trainer_dex_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN pokedex.catch_state_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE pokemon (id UUID NOT NULL, family_id UUID DEFAULT NULL, original_game_bundle_id UUID NOT NULL, variant_form_id UUID DEFAULT NULL, regional_form_id UUID DEFAULT NULL, special_form_id UUID DEFAULT NULL, category_form_id UUID DEFAULT NULL, primary_type_id UUID DEFAULT NULL, secondary_type_id UUID DEFAULT NULL, slug VARCHAR(255) NOT NULL, simplified_name VARCHAR(255) NOT NULL, simplified_french_name VARCHAR(255) NOT NULL, forms_label VARCHAR(255) NOT NULL, forms_french_label VARCHAR(255) NOT NULL, national_dex_number INT NOT NULL, prime_name VARCHAR(255) NOT NULL, bankable BOOLEAN NOT NULL, bankableish BOOLEAN DEFAULT NULL, icon_name VARCHAR(255) NOT NULL, family_order INT NOT NULL, name VARCHAR(255) NOT NULL, french_name VARCHAR(255) NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_62DC90F3989D9B62 ON pokemon (slug)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_62DC90F35E237E06 ON pokemon (name)');
        $this->addSql('CREATE INDEX IDX_62DC90F3C35E566A ON pokemon (family_id)');
        $this->addSql('CREATE INDEX IDX_62DC90F3D847356D ON pokemon (original_game_bundle_id)');
        $this->addSql('CREATE INDEX IDX_62DC90F33053B63B ON pokemon (variant_form_id)');
        $this->addSql('CREATE INDEX IDX_62DC90F3373825C8 ON pokemon (regional_form_id)');
        $this->addSql('CREATE INDEX IDX_62DC90F387FAC7E8 ON pokemon (special_form_id)');
        $this->addSql('CREATE INDEX IDX_62DC90F3982EDB03 ON pokemon (category_form_id)');
        $this->addSql('CREATE INDEX IDX_62DC90F3ED5C8A7 ON pokemon (primary_type_id)');
        $this->addSql('CREATE INDEX IDX_62DC90F3527A53 ON pokemon (secondary_type_id)');
        $this->addSql('COMMENT ON COLUMN pokemon.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN pokemon.family_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN pokemon.original_game_bundle_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN pokemon.variant_form_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN pokemon.regional_form_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN pokemon.special_form_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN pokemon.category_form_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN pokemon.primary_type_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN pokemon.secondary_type_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE region (id UUID NOT NULL, name VARCHAR(255) NOT NULL, french_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, order_number INT NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F62F1765E237E06 ON region (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F62F176989D9B62 ON region (slug)');
        $this->addSql('COMMENT ON COLUMN region.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE regional_dex_number (id UUID NOT NULL, region_id UUID NOT NULL, pokemon_name VARCHAR(255) NOT NULL, dex_number INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6507F50998260155 ON regional_dex_number (region_id)');
        $this->addSql('COMMENT ON COLUMN regional_dex_number.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN regional_dex_number.region_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE regional_form (id UUID NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, order_number INT NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ECF336915E237E06 ON regional_form (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ECF33691989D9B62 ON regional_form (slug)');
        $this->addSql('COMMENT ON COLUMN regional_form.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE special_form (id UUID NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, order_number INT NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D0DFFC645E237E06 ON special_form (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D0DFFC64989D9B62 ON special_form (slug)');
        $this->addSql('COMMENT ON COLUMN special_form.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE trainer_dex (id UUID NOT NULL, dex_id UUID NOT NULL, trainer_external_id VARCHAR(255) NOT NULL, is_private BOOLEAN DEFAULT true NOT NULL, is_on_home BOOLEAN DEFAULT false NOT NULL, name VARCHAR(255) NOT NULL, french_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C93AEB5544FE8083 ON trainer_dex (dex_id)');
        $this->addSql('CREATE UNIQUE INDEX trainers_dex ON trainer_dex (trainer_external_id, slug)');
        $this->addSql('COMMENT ON COLUMN trainer_dex.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN trainer_dex.dex_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE type (id UUID NOT NULL, name VARCHAR(255) NOT NULL, french_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, order_number INT NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, color VARCHAR(255) DEFAULT \'\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8CDE57295E237E06 ON type (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8CDE5729989D9B62 ON type (slug)');
        $this->addSql('COMMENT ON COLUMN type.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE variant_form (id UUID NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, order_number INT NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BD706BDA5E237E06 ON variant_form (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BD706BDA989D9B62 ON variant_form (slug)');
        $this->addSql('COMMENT ON COLUMN variant_form.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE dex ADD CONSTRAINT FK_F6CBDC0298260155 FOREIGN KEY (region_id) REFERENCES region (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dex_availability ADD CONSTRAINT FK_C3DC5ADA2FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dex_availability ADD CONSTRAINT FK_C3DC5ADA44FE8083 FOREIGN KEY (dex_id) REFERENCES dex (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CF1FAD9D3 FOREIGN KEY (bundle_id) REFERENCES game_bundle (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_availability ADD CONSTRAINT FK_D16796D1E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_bundle ADD CONSTRAINT FK_D84E9F35553A6EC4 FOREIGN KEY (generation_id) REFERENCES game_generation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_bundle_availability ADD CONSTRAINT FK_A9097E132FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_bundle_availability ADD CONSTRAINT FK_A9097E13F1FAD9D3 FOREIGN KEY (bundle_id) REFERENCES game_bundle (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_bundle_shiny_availability ADD CONSTRAINT FK_923E348F2FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_bundle_shiny_availability ADD CONSTRAINT FK_923E348FF1FAD9D3 FOREIGN KEY (bundle_id) REFERENCES game_bundle (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_shiny_availability ADD CONSTRAINT FK_57A1BEF6E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pokedex ADD CONSTRAINT FK_6336F6A72FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pokedex ADD CONSTRAINT FK_6336F6A777B0DA37 FOREIGN KEY (trainer_dex_id) REFERENCES trainer_dex (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pokedex ADD CONSTRAINT FK_6336F6A71339B3D7 FOREIGN KEY (catch_state_id) REFERENCES catch_state (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F3C35E566A FOREIGN KEY (family_id) REFERENCES pokemon (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F3D847356D FOREIGN KEY (original_game_bundle_id) REFERENCES game_bundle (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F33053B63B FOREIGN KEY (variant_form_id) REFERENCES variant_form (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F3373825C8 FOREIGN KEY (regional_form_id) REFERENCES regional_form (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F387FAC7E8 FOREIGN KEY (special_form_id) REFERENCES special_form (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F3982EDB03 FOREIGN KEY (category_form_id) REFERENCES category_form (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F3ED5C8A7 FOREIGN KEY (primary_type_id) REFERENCES type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F3527A53 FOREIGN KEY (secondary_type_id) REFERENCES type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE regional_dex_number ADD CONSTRAINT FK_6507F50998260155 FOREIGN KEY (region_id) REFERENCES region (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trainer_dex ADD CONSTRAINT FK_C93AEB5544FE8083 FOREIGN KEY (dex_id) REFERENCES dex (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE dex DROP CONSTRAINT FK_F6CBDC0298260155');
        $this->addSql('ALTER TABLE dex_availability DROP CONSTRAINT FK_C3DC5ADA2FE71C3E');
        $this->addSql('ALTER TABLE dex_availability DROP CONSTRAINT FK_C3DC5ADA44FE8083');
        $this->addSql('ALTER TABLE game DROP CONSTRAINT FK_232B318CF1FAD9D3');
        $this->addSql('ALTER TABLE game_availability DROP CONSTRAINT FK_D16796D1E48FD905');
        $this->addSql('ALTER TABLE game_bundle DROP CONSTRAINT FK_D84E9F35553A6EC4');
        $this->addSql('ALTER TABLE game_bundle_availability DROP CONSTRAINT FK_A9097E132FE71C3E');
        $this->addSql('ALTER TABLE game_bundle_availability DROP CONSTRAINT FK_A9097E13F1FAD9D3');
        $this->addSql('ALTER TABLE game_bundle_shiny_availability DROP CONSTRAINT FK_923E348F2FE71C3E');
        $this->addSql('ALTER TABLE game_bundle_shiny_availability DROP CONSTRAINT FK_923E348FF1FAD9D3');
        $this->addSql('ALTER TABLE game_shiny_availability DROP CONSTRAINT FK_57A1BEF6E48FD905');
        $this->addSql('ALTER TABLE pokedex DROP CONSTRAINT FK_6336F6A72FE71C3E');
        $this->addSql('ALTER TABLE pokedex DROP CONSTRAINT FK_6336F6A777B0DA37');
        $this->addSql('ALTER TABLE pokedex DROP CONSTRAINT FK_6336F6A71339B3D7');
        $this->addSql('ALTER TABLE pokemon DROP CONSTRAINT FK_62DC90F3C35E566A');
        $this->addSql('ALTER TABLE pokemon DROP CONSTRAINT FK_62DC90F3D847356D');
        $this->addSql('ALTER TABLE pokemon DROP CONSTRAINT FK_62DC90F33053B63B');
        $this->addSql('ALTER TABLE pokemon DROP CONSTRAINT FK_62DC90F3373825C8');
        $this->addSql('ALTER TABLE pokemon DROP CONSTRAINT FK_62DC90F387FAC7E8');
        $this->addSql('ALTER TABLE pokemon DROP CONSTRAINT FK_62DC90F3982EDB03');
        $this->addSql('ALTER TABLE pokemon DROP CONSTRAINT FK_62DC90F3ED5C8A7');
        $this->addSql('ALTER TABLE pokemon DROP CONSTRAINT FK_62DC90F3527A53');
        $this->addSql('ALTER TABLE regional_dex_number DROP CONSTRAINT FK_6507F50998260155');
        $this->addSql('ALTER TABLE trainer_dex DROP CONSTRAINT FK_C93AEB5544FE8083');
        $this->addSql('DROP TABLE action_log');
        $this->addSql('DROP TABLE catch_state');
        $this->addSql('DROP TABLE category_form');
        $this->addSql('DROP TABLE dex');
        $this->addSql('DROP TABLE dex_availability');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE game_availability');
        $this->addSql('DROP TABLE game_bundle');
        $this->addSql('DROP TABLE game_bundle_availability');
        $this->addSql('DROP TABLE game_bundle_shiny_availability');
        $this->addSql('DROP TABLE game_generation');
        $this->addSql('DROP TABLE game_shiny_availability');
        $this->addSql('DROP TABLE pokedex');
        $this->addSql('DROP TABLE pokemon');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE regional_dex_number');
        $this->addSql('DROP TABLE regional_form');
        $this->addSql('DROP TABLE special_form');
        $this->addSql('DROP TABLE trainer_dex');
        $this->addSql('DROP TABLE type');
        $this->addSql('DROP TABLE variant_form');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
