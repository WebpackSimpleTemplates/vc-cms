<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251029144501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quality_response (id SERIAL NOT NULL, call_id UUID NOT NULL, quality_id INT NOT NULL, channel_id INT NOT NULL, consultant_id INT DEFAULT NULL, value INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2A1B838F50A89B2C ON quality_response (call_id)');
        $this->addSql('CREATE INDEX IDX_2A1B838FBCFC6D57 ON quality_response (quality_id)');
        $this->addSql('CREATE INDEX IDX_2A1B838F72F5A1AA ON quality_response (channel_id)');
        $this->addSql('CREATE INDEX IDX_2A1B838F44F779A2 ON quality_response (consultant_id)');
        $this->addSql('COMMENT ON COLUMN quality_response.call_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE quality_response ADD CONSTRAINT FK_2A1B838F50A89B2C FOREIGN KEY (call_id) REFERENCES call (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quality_response ADD CONSTRAINT FK_2A1B838FBCFC6D57 FOREIGN KEY (quality_id) REFERENCES quality (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quality_response ADD CONSTRAINT FK_2A1B838F72F5A1AA FOREIGN KEY (channel_id) REFERENCES channel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quality_response ADD CONSTRAINT FK_2A1B838F44F779A2 FOREIGN KEY (consultant_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE quality_response DROP CONSTRAINT FK_2A1B838F50A89B2C');
        $this->addSql('ALTER TABLE quality_response DROP CONSTRAINT FK_2A1B838FBCFC6D57');
        $this->addSql('ALTER TABLE quality_response DROP CONSTRAINT FK_2A1B838F72F5A1AA');
        $this->addSql('ALTER TABLE quality_response DROP CONSTRAINT FK_2A1B838F44F779A2');
        $this->addSql('DROP TABLE quality_response');
    }
}
