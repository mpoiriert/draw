<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191023185421 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE draw_messenger__message');
        $this->addSql('ALTER TABLE 
          draw_acme__user 
        ADD 
          last_password_updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE draw_messenger__message (
          id CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', 
          body LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, 
          headers LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, 
          queue_name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, 
          created_at DATETIME NOT NULL, 
          available_at DATETIME DEFAULT NULL, 
          delivered_at DATETIME DEFAULT NULL, 
          INDEX IDX_7403A373E3BD61CE (available_at), 
          INDEX IDX_7403A373FB7336F0 (queue_name), 
          INDEX IDX_7403A37316BA31DB (delivered_at), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE draw_acme__user DROP last_password_updated_at');
    }
}
