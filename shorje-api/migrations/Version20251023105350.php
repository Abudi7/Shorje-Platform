<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251023105350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blog_posts (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, excerpt VARCHAR(255) DEFAULT NULL, featured_image VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) DEFAULT NULL, is_published TINYINT(1) NOT NULL, view_count INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, published_at DATETIME DEFAULT NULL, INDEX IDX_78B2F932F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE faqs (id INT AUTO_INCREMENT NOT NULL, question VARCHAR(255) NOT NULL, answer LONGTEXT NOT NULL, category VARCHAR(100) DEFAULT NULL, sort_order INT NOT NULL, is_active TINYINT(1) NOT NULL, view_count INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE help_center (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, category VARCHAR(100) DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, sort_order INT NOT NULL, is_active TINYINT(1) NOT NULL, view_count INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE terms_and_conditions (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, version VARCHAR(50) NOT NULL, is_active TINYINT(1) NOT NULL, is_current TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, effective_date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blog_posts ADD CONSTRAINT FK_78B2F932F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE messages ADD attachment_path VARCHAR(255) DEFAULT NULL, ADD is_voice TINYINT(1) NOT NULL, ADD voice_duration INT DEFAULT NULL, ADD is_video_call TINYINT(1) NOT NULL, ADD call_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog_posts DROP FOREIGN KEY FK_78B2F932F675F31B');
        $this->addSql('DROP TABLE blog_posts');
        $this->addSql('DROP TABLE faqs');
        $this->addSql('DROP TABLE help_center');
        $this->addSql('DROP TABLE terms_and_conditions');
        $this->addSql('ALTER TABLE messages DROP attachment_path, DROP is_voice, DROP voice_duration, DROP is_video_call, DROP call_id');
    }
}
