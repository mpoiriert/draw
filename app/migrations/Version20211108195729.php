<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211108195729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE command__execution (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          command VARCHAR(40) DEFAULT \'N/A\' NOT NULL,
          command_name VARCHAR(255) NOT NULL,
          state VARCHAR(40) NOT NULL,
          input JSON NOT NULL COMMENT \'(DC2Type:json_array)\',
          output LONGTEXT NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX state (state),
          INDEX command (command),
          INDEX command_name (command_name),
          INDEX state_updated (state, updated_at),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE command__execution');
    }
}
