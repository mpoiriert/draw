<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200413155805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_tag (
          user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\',
          tag_id INT NOT NULL,
          INDEX IDX_E89FD608A76ED395 (user_id),
          INDEX IDX_E89FD608BAD26311 (tag_id),
          PRIMARY KEY(user_id, tag_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          user_tag
        ADD
          CONSTRAINT FK_E89FD608A76ED395 FOREIGN KEY (user_id) REFERENCES draw_acme__user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          user_tag
        ADD
          CONSTRAINT FK_E89FD608BAD26311 FOREIGN KEY (tag_id) REFERENCES draw_acme__tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_tag');
    }
}
