<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240411203316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE import__column (
          id INT AUTO_INCREMENT NOT NULL,
          import_id INT UNSIGNED NOT NULL,
          header_name VARCHAR(255) NOT NULL,
          sample LONGTEXT NOT NULL,
          is_identifier TINYINT(1) DEFAULT 0 NOT NULL,
          is_ignored TINYINT(1) DEFAULT 0 NOT NULL,
          mapped_to VARCHAR(255) DEFAULT NULL,
          is_date TINYINT(1) DEFAULT 0 NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_9770C911B6A263D9 (import_id),
          UNIQUE INDEX import_header_name (import_id, header_name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE import__import (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          entity_class VARCHAR(255) NOT NULL,
          file_content LONGTEXT DEFAULT NULL,
          state VARCHAR(40) DEFAULT \'new\' NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          import__column
        ADD
          CONSTRAINT FK_9770C911B6A263D9 FOREIGN KEY (import_id) REFERENCES import__import (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE import__column DROP FOREIGN KEY FK_9770C911B6A263D9');
        $this->addSql('DROP TABLE import__column');
        $this->addSql('DROP TABLE import__import');
    }
}
