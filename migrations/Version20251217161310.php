<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217161310 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quiz_response (id SERIAL NOT NULL, channel_id INT NOT NULL, call_id UUID NOT NULL, consultant_id INT NOT NULL, quiz_id INT NOT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E8BFF2BE72F5A1AA ON quiz_response (channel_id)');
        $this->addSql('CREATE INDEX IDX_E8BFF2BE50A89B2C ON quiz_response (call_id)');
        $this->addSql('CREATE INDEX IDX_E8BFF2BE44F779A2 ON quiz_response (consultant_id)');
        $this->addSql('CREATE INDEX IDX_E8BFF2BE853CD175 ON quiz_response (quiz_id)');
        $this->addSql('COMMENT ON COLUMN quiz_response.call_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE quiz_response ADD CONSTRAINT FK_E8BFF2BE72F5A1AA FOREIGN KEY (channel_id) REFERENCES channel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quiz_response ADD CONSTRAINT FK_E8BFF2BE50A89B2C FOREIGN KEY (call_id) REFERENCES call (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quiz_response ADD CONSTRAINT FK_E8BFF2BE44F779A2 FOREIGN KEY (consultant_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quiz_response ADD CONSTRAINT FK_E8BFF2BE853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE quiz_response DROP CONSTRAINT FK_E8BFF2BE72F5A1AA');
        $this->addSql('ALTER TABLE quiz_response DROP CONSTRAINT FK_E8BFF2BE50A89B2C');
        $this->addSql('ALTER TABLE quiz_response DROP CONSTRAINT FK_E8BFF2BE44F779A2');
        $this->addSql('ALTER TABLE quiz_response DROP CONSTRAINT FK_E8BFF2BE853CD175');
        $this->addSql('DROP TABLE quiz_response');
    }
}
