<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230731181203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE acme__user_tag (
          id INT AUTO_INCREMENT NOT NULL,
          user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\',
          tag_id BIGINT NOT NULL,
          INDEX IDX_8C67AC97A76ED395 (user_id),
          INDEX IDX_8C67AC97BAD26311 (tag_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          acme__user_tag
        ADD
          CONSTRAINT FK_8C67AC97A76ED395 FOREIGN KEY (user_id) REFERENCES draw_acme__user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          acme__user_tag
        ADD
          CONSTRAINT FK_8C67AC97BAD26311 FOREIGN KEY (tag_id) REFERENCES draw_acme__tag (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
    }
}
