<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251011103652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE consultant_status (id SERIAL NOT NULL, user_link_id INT NOT NULL, status VARCHAR(255) NOT NULL, pause_time INT NOT NULL, wait_time INT NOT NULL, serve_time INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3EC1E41EF5A91C7B ON consultant_status (user_link_id)');
        $this->addSql('ALTER TABLE consultant_status ADD CONSTRAINT FK_3EC1E41EF5A91C7B FOREIGN KEY (user_link_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE consultant_status DROP CONSTRAINT FK_3EC1E41EF5A91C7B');
        $this->addSql('DROP TABLE consultant_status');
    }
}
