<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251105140347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE call ADD redirected_from_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE call ADD redirected_to_channel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE call ADD redirected_to_consultant_id INT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN call.redirected_from_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE call ADD CONSTRAINT FK_CC8E2F3EA3DF6127 FOREIGN KEY (redirected_from_id) REFERENCES call (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE call ADD CONSTRAINT FK_CC8E2F3E6C662F64 FOREIGN KEY (redirected_to_channel_id) REFERENCES channel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE call ADD CONSTRAINT FK_CC8E2F3E13C0B5CD FOREIGN KEY (redirected_to_consultant_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CC8E2F3EA3DF6127 ON call (redirected_from_id)');
        $this->addSql('CREATE INDEX IDX_CC8E2F3E6C662F64 ON call (redirected_to_channel_id)');
        $this->addSql('CREATE INDEX IDX_CC8E2F3E13C0B5CD ON call (redirected_to_consultant_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE call DROP CONSTRAINT FK_CC8E2F3EA3DF6127');
        $this->addSql('ALTER TABLE call DROP CONSTRAINT FK_CC8E2F3E6C662F64');
        $this->addSql('ALTER TABLE call DROP CONSTRAINT FK_CC8E2F3E13C0B5CD');
        $this->addSql('DROP INDEX UNIQ_CC8E2F3EA3DF6127');
        $this->addSql('DROP INDEX IDX_CC8E2F3E6C662F64');
        $this->addSql('DROP INDEX IDX_CC8E2F3E13C0B5CD');
        $this->addSql('ALTER TABLE call DROP redirected_from_id');
        $this->addSql('ALTER TABLE call DROP redirected_to_channel_id');
        $this->addSql('ALTER TABLE call DROP redirected_to_consultant_id');
    }
}
