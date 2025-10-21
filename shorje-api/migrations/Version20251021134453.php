<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251021134453 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE messages ADD attachment LONGBLOB DEFAULT NULL, ADD attachment_mime_type VARCHAR(100) DEFAULT NULL, ADD attachment_name VARCHAR(255) DEFAULT NULL, ADD seen_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD is_online TINYINT(1) NOT NULL, ADD last_seen_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE messages DROP attachment, DROP attachment_mime_type, DROP attachment_name, DROP seen_at');
        $this->addSql('ALTER TABLE user DROP is_online, DROP last_seen_at');
    }
}
