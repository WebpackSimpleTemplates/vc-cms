<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251011121019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE call ADD channel_id INT NOT NULL');
        $this->addSql('ALTER TABLE call ADD CONSTRAINT FK_CC8E2F3E72F5A1AA FOREIGN KEY (channel_id) REFERENCES channel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_CC8E2F3E72F5A1AA ON call (channel_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE call DROP CONSTRAINT FK_CC8E2F3E72F5A1AA');
        $this->addSql('DROP INDEX IDX_CC8E2F3E72F5A1AA');
        $this->addSql('ALTER TABLE call DROP channel_id');
    }
}
