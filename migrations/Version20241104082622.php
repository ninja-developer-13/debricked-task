<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241104082622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dependency_file_upload ADD commit_name VARCHAR(255) DEFAULT NULL, ADD repository_url VARCHAR(255) DEFAULT NULL, ADD repository_name VARCHAR(255) DEFAULT NULL, ADD file_relative_path VARCHAR(255) DEFAULT NULL, ADD branch_name VARCHAR(255) DEFAULT NULL, ADD default_branch_name VARCHAR(255) DEFAULT NULL, ADD release_name VARCHAR(255) DEFAULT NULL, ADD product_name VARCHAR(255) DEFAULT NULL, CHANGE ci_upload_id ci_upload_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dependency_file_upload DROP commit_name, DROP repository_url, DROP repository_name, DROP file_relative_path, DROP branch_name, DROP default_branch_name, DROP release_name, DROP product_name, CHANGE ci_upload_id ci_upload_id VARCHAR(255) NOT NULL');
    }
}
