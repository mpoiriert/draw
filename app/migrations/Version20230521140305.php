<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230521140305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE
          command__execution
        CHANGE
          created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
        CHANGE
          updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user_tag DROP FOREIGN KEY FK_E89FD608BAD26311');
        $this->addSql('ALTER TABLE draw_acme__tag CHANGE id id BIGINT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE user_tag CHANGE tag_id tag_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE
          user_tag
        ADD
          CONSTRAINT FK_E89FD608BAD26311 FOREIGN KEY (tag_id) REFERENCES draw_acme__tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE draw_acme__tag CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE
          command__execution
        CHANGE
          created_at created_at DATETIME NOT NULL,
        CHANGE
          updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_tag CHANGE tag_id tag_id INT NOT NULL');
    }
}
