<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251016122703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD profile_picture_mime_type VARCHAR(100) DEFAULT NULL, ADD cover_image_mime_type VARCHAR(100) DEFAULT NULL, CHANGE profile_picture profile_picture LONGBLOB DEFAULT NULL, CHANGE cover_image cover_image LONGBLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP profile_picture_mime_type, DROP cover_image_mime_type, CHANGE profile_picture profile_picture VARCHAR(500) DEFAULT NULL, CHANGE cover_image cover_image VARCHAR(500) DEFAULT NULL');
    }
}
