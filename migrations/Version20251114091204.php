<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114091204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE call_user (call_id UUID NOT NULL, user_id INT NOT NULL, PRIMARY KEY(call_id, user_id))');
        $this->addSql('CREATE INDEX IDX_BA12B11550A89B2C ON call_user (call_id)');
        $this->addSql('CREATE INDEX IDX_BA12B115A76ED395 ON call_user (user_id)');
        $this->addSql('COMMENT ON COLUMN call_user.call_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE call_user ADD CONSTRAINT FK_BA12B11550A89B2C FOREIGN KEY (call_id) REFERENCES call (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE call_user ADD CONSTRAINT FK_BA12B115A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE call ADD client_is_connected BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE call_user DROP CONSTRAINT FK_BA12B11550A89B2C');
        $this->addSql('ALTER TABLE call_user DROP CONSTRAINT FK_BA12B115A76ED395');
        $this->addSql('DROP TABLE call_user');
        $this->addSql('ALTER TABLE call DROP client_is_connected');
    }
}
