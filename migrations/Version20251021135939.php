<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251021135939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE history_log (id SERIAL NOT NULL, usr_id INT NOT NULL, datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, action VARCHAR(255) NOT NULL, params VARCHAR(255) NOT NULL, is_consultant BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6190350AC69D3FB ON history_log (usr_id)');
        $this->addSql('ALTER TABLE history_log ADD CONSTRAINT FK_6190350AC69D3FB FOREIGN KEY (usr_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE history_log DROP CONSTRAINT FK_6190350AC69D3FB');
        $this->addSql('DROP TABLE history_log');
    }
}
