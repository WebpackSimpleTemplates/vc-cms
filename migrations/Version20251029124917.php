<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251029124917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quality (id SERIAL NOT NULL, is_main BOOLEAN NOT NULL, is_consultant BOOLEAN NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE quality_channel (quality_id INT NOT NULL, channel_id INT NOT NULL, PRIMARY KEY(quality_id, channel_id))');
        $this->addSql('CREATE INDEX IDX_C32DB55BBCFC6D57 ON quality_channel (quality_id)');
        $this->addSql('CREATE INDEX IDX_C32DB55B72F5A1AA ON quality_channel (channel_id)');
        $this->addSql('CREATE TABLE quality_user (quality_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(quality_id, user_id))');
        $this->addSql('CREATE INDEX IDX_2207AA55BCFC6D57 ON quality_user (quality_id)');
        $this->addSql('CREATE INDEX IDX_2207AA55A76ED395 ON quality_user (user_id)');
        $this->addSql('ALTER TABLE quality_channel ADD CONSTRAINT FK_C32DB55BBCFC6D57 FOREIGN KEY (quality_id) REFERENCES quality (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quality_channel ADD CONSTRAINT FK_C32DB55B72F5A1AA FOREIGN KEY (channel_id) REFERENCES channel (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quality_user ADD CONSTRAINT FK_2207AA55BCFC6D57 FOREIGN KEY (quality_id) REFERENCES quality (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quality_user ADD CONSTRAINT FK_2207AA55A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE quality_channel DROP CONSTRAINT FK_C32DB55BBCFC6D57');
        $this->addSql('ALTER TABLE quality_channel DROP CONSTRAINT FK_C32DB55B72F5A1AA');
        $this->addSql('ALTER TABLE quality_user DROP CONSTRAINT FK_2207AA55BCFC6D57');
        $this->addSql('ALTER TABLE quality_user DROP CONSTRAINT FK_2207AA55A76ED395');
        $this->addSql('DROP TABLE quality');
        $this->addSql('DROP TABLE quality_channel');
        $this->addSql('DROP TABLE quality_user');
    }
}
