<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251021091452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE message (id SERIAL NOT NULL, call_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, message TEXT DEFAULT NULL, author_id INT DEFAULT NULL, status SMALLINT NOT NULL, file_size VARCHAR(255) DEFAULT NULL, file_path VARCHAR(255) DEFAULT NULL, file_name VARCHAR(255) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6BD307F50A89B2C ON message (call_id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F50A89B2C FOREIGN KEY (call_id) REFERENCES call (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F50A89B2C');
        $this->addSql('DROP TABLE message');
    }
}
