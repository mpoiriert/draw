<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200419202333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE acme__user_address (
          id INT AUTO_INCREMENT NOT NULL, 
          user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', 
          address_street VARCHAR(255) DEFAULT \'\' NOT NULL, 
          address_postal_code VARCHAR(255) DEFAULT \'\' NOT NULL, 
          address_city VARCHAR(255) DEFAULT \'\' NOT NULL, 
          address_country VARCHAR(255) DEFAULT \'\' NOT NULL, 
          INDEX IDX_7FBCA30BA76ED395 (user_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          acme__user_address 
        ADD 
          CONSTRAINT FK_7FBCA30BA76ED395 FOREIGN KEY (user_id) REFERENCES draw_acme__user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE acme__user_address');
    }
}
