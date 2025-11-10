<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110083338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE channel ADD deleted_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT FK_A2F98E47C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A2F98E47C76F1F52 ON channel (deleted_by_id)');
        $this->addSql('ALTER TABLE quality ADD deleted_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quality ADD CONSTRAINT FK_7CB20B10C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7CB20B10C76F1F52 ON quality (deleted_by_id)');
        $this->addSql('ALTER TABLE "user" ADD deleted_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8D93D649C76F1F52 ON "user" (deleted_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE quality DROP CONSTRAINT FK_7CB20B10C76F1F52');
        $this->addSql('DROP INDEX IDX_7CB20B10C76F1F52');
        $this->addSql('ALTER TABLE quality DROP deleted_by_id');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649C76F1F52');
        $this->addSql('DROP INDEX IDX_8D93D649C76F1F52');
        $this->addSql('ALTER TABLE "user" DROP deleted_by_id');
        $this->addSql('ALTER TABLE channel DROP CONSTRAINT FK_A2F98E47C76F1F52');
        $this->addSql('DROP INDEX IDX_A2F98E47C76F1F52');
        $this->addSql('ALTER TABLE channel DROP deleted_by_id');
    }
}
