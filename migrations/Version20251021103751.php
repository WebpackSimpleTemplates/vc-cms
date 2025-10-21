<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251021103751 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE call (id UUID NOT NULL, consultant_id INT DEFAULT NULL, channel_id INT NOT NULL, prefix VARCHAR(255) NOT NULL, num INT NOT NULL, type VARCHAR(255) NOT NULL, wait_start TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, accepted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, closed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CC8E2F3E44F779A2 ON call (consultant_id)');
        $this->addSql('CREATE INDEX IDX_CC8E2F3E72F5A1AA ON call (channel_id)');
        $this->addSql('COMMENT ON COLUMN call.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE channel (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, prefix VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE consultant_status (id SERIAL NOT NULL, user_link_id INT NOT NULL, call_id UUID DEFAULT NULL, status VARCHAR(255) NOT NULL, pause_time INT NOT NULL, wait_time INT NOT NULL, serve_time INT NOT NULL, last_online TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3EC1E41EF5A91C7B ON consultant_status (user_link_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3EC1E41E50A89B2C ON consultant_status (call_id)');
        $this->addSql('COMMENT ON COLUMN consultant_status.call_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE message (id SERIAL NOT NULL, call_id UUID NOT NULL, name VARCHAR(255) DEFAULT NULL, message TEXT DEFAULT NULL, author_id INT DEFAULT NULL, status SMALLINT NOT NULL, file_size VARCHAR(255) DEFAULT NULL, file_path VARCHAR(255) DEFAULT NULL, file_name VARCHAR(255) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, time_stamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6BD307F50A89B2C ON message (call_id)');
        $this->addSql('COMMENT ON COLUMN message.call_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, display_name VARCHAR(255) DEFAULT NULL, fullname VARCHAR(255) DEFAULT NULL, avatar TEXT DEFAULT NULL, is_consultant BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('CREATE TABLE user_channel (user_id INT NOT NULL, channel_id INT NOT NULL, PRIMARY KEY(user_id, channel_id))');
        $this->addSql('CREATE INDEX IDX_FAF4904DA76ED395 ON user_channel (user_id)');
        $this->addSql('CREATE INDEX IDX_FAF4904D72F5A1AA ON user_channel (channel_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE call ADD CONSTRAINT FK_CC8E2F3E44F779A2 FOREIGN KEY (consultant_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE call ADD CONSTRAINT FK_CC8E2F3E72F5A1AA FOREIGN KEY (channel_id) REFERENCES channel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE consultant_status ADD CONSTRAINT FK_3EC1E41EF5A91C7B FOREIGN KEY (user_link_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE consultant_status ADD CONSTRAINT FK_3EC1E41E50A89B2C FOREIGN KEY (call_id) REFERENCES call (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F50A89B2C FOREIGN KEY (call_id) REFERENCES call (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_channel ADD CONSTRAINT FK_FAF4904DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_channel ADD CONSTRAINT FK_FAF4904D72F5A1AA FOREIGN KEY (channel_id) REFERENCES channel (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE call DROP CONSTRAINT FK_CC8E2F3E44F779A2');
        $this->addSql('ALTER TABLE call DROP CONSTRAINT FK_CC8E2F3E72F5A1AA');
        $this->addSql('ALTER TABLE consultant_status DROP CONSTRAINT FK_3EC1E41EF5A91C7B');
        $this->addSql('ALTER TABLE consultant_status DROP CONSTRAINT FK_3EC1E41E50A89B2C');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F50A89B2C');
        $this->addSql('ALTER TABLE user_channel DROP CONSTRAINT FK_FAF4904DA76ED395');
        $this->addSql('ALTER TABLE user_channel DROP CONSTRAINT FK_FAF4904D72F5A1AA');
        $this->addSql('DROP TABLE call');
        $this->addSql('DROP TABLE channel');
        $this->addSql('DROP TABLE consultant_status');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE user_channel');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
