<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260726210102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE candidate_profile (id VARCHAR(36) NOT NULL, source_file_name VARCHAR(255) NOT NULL, extracted_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, seniority VARCHAR(20) NOT NULL, domains JSON NOT NULL, roles JSON NOT NULL, languages JSON NOT NULL, skills JSON NOT NULL, PRIMARY KEY (id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE candidate_profile');
    }
}
