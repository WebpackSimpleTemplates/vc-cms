<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217160605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quiz (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE quiz_channel (quiz_id INT NOT NULL, channel_id INT NOT NULL, PRIMARY KEY(quiz_id, channel_id))');
        $this->addSql('CREATE INDEX IDX_B0A323E7853CD175 ON quiz_channel (quiz_id)');
        $this->addSql('CREATE INDEX IDX_B0A323E772F5A1AA ON quiz_channel (channel_id)');
        $this->addSql('CREATE TABLE quiz_user (quiz_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(quiz_id, user_id))');
        $this->addSql('CREATE INDEX IDX_47622A12853CD175 ON quiz_user (quiz_id)');
        $this->addSql('CREATE INDEX IDX_47622A12A76ED395 ON quiz_user (user_id)');
        $this->addSql('ALTER TABLE quiz_channel ADD CONSTRAINT FK_B0A323E7853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quiz_channel ADD CONSTRAINT FK_B0A323E772F5A1AA FOREIGN KEY (channel_id) REFERENCES channel (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quiz_user ADD CONSTRAINT FK_47622A12853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quiz_user ADD CONSTRAINT FK_47622A12A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE quiz_channel DROP CONSTRAINT FK_B0A323E7853CD175');
        $this->addSql('ALTER TABLE quiz_channel DROP CONSTRAINT FK_B0A323E772F5A1AA');
        $this->addSql('ALTER TABLE quiz_user DROP CONSTRAINT FK_47622A12853CD175');
        $this->addSql('ALTER TABLE quiz_user DROP CONSTRAINT FK_47622A12A76ED395');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE quiz_channel');
        $this->addSql('DROP TABLE quiz_user');
    }
}
