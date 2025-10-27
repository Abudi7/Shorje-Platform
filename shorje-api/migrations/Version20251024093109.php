<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251024093109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_favorites (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, product_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_8AC9D678A76ED395 (user_id), INDEX IDX_8AC9D6784584665A (product_id), UNIQUE INDEX user_product_unique (user_id, product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_favorites ADD CONSTRAINT FK_8AC9D678A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_favorites ADD CONSTRAINT FK_8AC9D6784584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product DROP view_count');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_favorites DROP FOREIGN KEY FK_8AC9D678A76ED395');
        $this->addSql('ALTER TABLE product_favorites DROP FOREIGN KEY FK_8AC9D6784584665A');
        $this->addSql('DROP TABLE product_favorites');
        $this->addSql('ALTER TABLE product ADD view_count INT DEFAULT 0 NOT NULL');
    }
}
