<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260726010551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create catalog_job table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE catalog_job (id VARCHAR(20) NOT NULL, title VARCHAR(300) NOT NULL, description TEXT NOT NULL, company_name VARCHAR(255) NOT NULL, company_logo_url VARCHAR(500) DEFAULT NULL, company_description TEXT DEFAULT NULL, contract_type VARCHAR(20) NOT NULL, apply_url VARCHAR(500) NOT NULL, status VARCHAR(20) NOT NULL, location_label VARCHAR(255) DEFAULT NULL, location_insee_code VARCHAR(5) DEFAULT NULL, location_postal_code VARCHAR(5) DEFAULT NULL, rome_code VARCHAR(5) DEFAULT NULL, rome_label VARCHAR(255) DEFAULT NULL, experience_requirement VARCHAR(30) DEFAULT NULL, is_work_study BOOLEAN NOT NULL, salary_description TEXT DEFAULT NULL, number_of_positions INT NOT NULL, skills JSON NOT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE catalog_job');
    }
}
