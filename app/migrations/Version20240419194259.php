<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240419194259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tag_translation (
          id INT AUTO_INCREMENT NOT NULL,
          translatable_id BIGINT DEFAULT NULL,
          label VARCHAR(255) NOT NULL,
          locale VARCHAR(5) NOT NULL,
          INDEX IDX_A8A03F8F2C2AC5D3 (translatable_id),
          UNIQUE INDEX tag_translation_unique_translation (translatable_id, locale),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          tag_translation
        ADD
          CONSTRAINT FK_A8A03F8F2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES draw_acme__tag (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX UNIQ_C052A2E4EA750E8 ON draw_acme__tag');
        $this->addSql('ALTER TABLE draw_acme__tag CHANGE label name VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C052A2E45E237E06 ON draw_acme__tag (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag_translation DROP FOREIGN KEY FK_A8A03F8F2C2AC5D3');
        $this->addSql('DROP TABLE tag_translation');
        $this->addSql('DROP INDEX UNIQ_C052A2E45E237E06 ON draw_acme__tag');
        $this->addSql('ALTER TABLE draw_acme__tag CHANGE name label VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C052A2E4EA750E8 ON draw_acme__tag (label)');
    }
}
