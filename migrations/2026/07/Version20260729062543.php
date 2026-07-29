<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260729062543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Widen image_pipeline_run.workflow_arun_id/workflow_brun_id from INT to BIGINT: GitHub Actions run ids now exceed the 32-bit integer range';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE image_pipeline_run ALTER workflow_arun_id TYPE BIGINT');
        $this->addSql('ALTER TABLE image_pipeline_run ALTER workflow_brun_id TYPE BIGINT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE image_pipeline_run ALTER workflow_arun_id TYPE INT');
        $this->addSql('ALTER TABLE image_pipeline_run ALTER workflow_brun_id TYPE INT');
    }
}
