<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260813145221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add banner_pipeline_run table to track GitHub Actions workflow runs and PR states for the icon/resources banner pipeline';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE banner_pipeline_run (correlation_id VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, workflow_arun_id BIGINT DEFAULT NULL, workflow_astatus VARCHAR(255) DEFAULT NULL, workflow_aconclusion VARCHAR(255) DEFAULT NULL, workflow_aurl VARCHAR(255) DEFAULT NULL, icon_pr_number INT DEFAULT NULL, icon_pr_url VARCHAR(255) DEFAULT NULL, icon_pr_state VARCHAR(255) DEFAULT NULL, icon_pr_merge_commit_sha VARCHAR(255) DEFAULT NULL, workflow_brun_id BIGINT DEFAULT NULL, workflow_bstatus VARCHAR(255) DEFAULT NULL, workflow_bconclusion VARCHAR(255) DEFAULT NULL, workflow_burl VARCHAR(255) DEFAULT NULL, resources_pr_number INT DEFAULT NULL, resources_pr_url VARCHAR(255) DEFAULT NULL, resources_pr_state VARCHAR(255) DEFAULT NULL, id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9B98B1AA9924E382 ON banner_pipeline_run (correlation_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE banner_pipeline_run');
    }
}
