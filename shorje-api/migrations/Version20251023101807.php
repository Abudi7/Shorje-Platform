<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251023101807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D34584665A');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D38DE820D9');
        $this->addSql('DROP INDEX IDX_6000B0D34584665A ON notifications');
        $this->addSql('DROP INDEX IDX_6000B0D38DE820D9 ON notifications');
        $this->addSql('ALTER TABLE notifications ADD is_important TINYINT(1) NOT NULL, ADD action_url VARCHAR(255) DEFAULT NULL, ADD action_text VARCHAR(50) DEFAULT NULL, ADD icon VARCHAR(50) DEFAULT NULL, ADD color VARCHAR(20) DEFAULT NULL, ADD expires_at DATETIME DEFAULT NULL, DROP product_id, DROP seller_id, CHANGE message message LONGTEXT NOT NULL, CHANGE data metadata JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notifications ADD product_id INT DEFAULT NULL, ADD seller_id INT DEFAULT NULL, DROP is_important, DROP action_url, DROP action_text, DROP icon, DROP color, DROP expires_at, CHANGE message message LONGTEXT DEFAULT NULL, CHANGE metadata data JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D34584665A FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D38DE820D9 FOREIGN KEY (seller_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6000B0D34584665A ON notifications (product_id)');
        $this->addSql('CREATE INDEX IDX_6000B0D38DE820D9 ON notifications (seller_id)');
    }
}
