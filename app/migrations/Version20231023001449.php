<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231023001449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE draw_entity_migrator__migration (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) NOT NULL,
          state VARCHAR(255) NOT NULL,
          UNIQUE INDEX name (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_migration (
          id BIGINT AUTO_INCREMENT NOT NULL,
          entity_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\',
          migration_id INT NOT NULL,
          state VARCHAR(255) DEFAULT \'new\' NOT NULL,
          transition_logs JSON DEFAULT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_C3FC382681257D5D (entity_id),
          INDEX IDX_C3FC382679D9816F (migration_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          user_migration
        ADD
          CONSTRAINT FK_C3FC382681257D5D FOREIGN KEY (entity_id) REFERENCES draw_acme__user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          user_migration
        ADD
          CONSTRAINT FK_C3FC382679D9816F FOREIGN KEY (migration_id) REFERENCES draw_entity_migrator__migration (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_migration DROP FOREIGN KEY FK_C3FC382681257D5D');
        $this->addSql('ALTER TABLE user_migration DROP FOREIGN KEY FK_C3FC382679D9816F');
        $this->addSql('DROP TABLE draw_entity_migrator__migration');
        $this->addSql('DROP TABLE user_migration');
    }
}
