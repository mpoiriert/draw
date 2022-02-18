<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220218192808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE draw_acme__user ADD has_manual_lock TINYINT(1) DEFAULT \'0\' NOT NULL');

        $this->addSql('CREATE TABLE draw_user__user_lock (
          id INT AUTO_INCREMENT NOT NULL,
          user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\',
          reason VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          lock_on DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          unlock_until DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_A86CF708A76ED395 (user_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          draw_user__user_lock
        ADD
          CONSTRAINT FK_A86CF708A76ED395 FOREIGN KEY (user_id) REFERENCES draw_acme__user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
    }
}
