<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251011104956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consultant_status ADD call_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE consultant_status ADD CONSTRAINT FK_3EC1E41E50A89B2C FOREIGN KEY (call_id) REFERENCES call (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3EC1E41E50A89B2C ON consultant_status (call_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE consultant_status DROP CONSTRAINT FK_3EC1E41E50A89B2C');
        $this->addSql('DROP INDEX UNIQ_3EC1E41E50A89B2C');
        $this->addSql('ALTER TABLE consultant_status DROP call_id');
    }
}
