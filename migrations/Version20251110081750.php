<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110081750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE channel ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('DROP INDEX uniq_3ec1e41e50a89b2c');
        $this->addSql('CREATE INDEX IDX_3EC1E41E50A89B2C ON consultant_status (call_id)');
        $this->addSql('ALTER TABLE "user" ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE channel DROP deleted_at');
        $this->addSql('DROP INDEX IDX_3EC1E41E50A89B2C');
        $this->addSql('CREATE UNIQUE INDEX uniq_3ec1e41e50a89b2c ON consultant_status (call_id)');
        $this->addSql('ALTER TABLE "user" DROP deleted_at');
    }
}
