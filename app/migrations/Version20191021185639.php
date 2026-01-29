<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191021185639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql('CREATE TABLE draw_acme__user (
          id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\',
          email VARCHAR(180) NOT NULL,
          roles JSON NOT NULL,
          password VARCHAR(255) NOT NULL,
          UNIQUE INDEX UNIQ_5E86F9A7E7927C74 (email),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql('DROP TABLE draw_acme__user');
    }
}
