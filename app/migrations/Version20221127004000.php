<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221127004000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE
          draw_acme__user
        ADD
          child_object1_id INT DEFAULT NULL,
        ADD
          child_object2_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          draw_acme__user
        ADD
          CONSTRAINT FK_5E86F9A79E2C07EE FOREIGN KEY (child_object1_id) REFERENCES draw_acme__base_object (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          draw_acme__user
        ADD
          CONSTRAINT FK_5E86F9A78C99A800 FOREIGN KEY (child_object2_id) REFERENCES draw_acme__base_object (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_5E86F9A79E2C07EE ON draw_acme__user (child_object1_id)');
        $this->addSql('CREATE INDEX IDX_5E86F9A78C99A800 ON draw_acme__user (child_object2_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE draw_acme__user DROP FOREIGN KEY FK_5E86F9A79E2C07EE');
        $this->addSql('ALTER TABLE draw_acme__user DROP FOREIGN KEY FK_5E86F9A78C99A800');
        $this->addSql('DROP INDEX IDX_5E86F9A79E2C07EE ON draw_acme__user');
        $this->addSql('DROP INDEX IDX_5E86F9A78C99A800 ON draw_acme__user');
        $this->addSql('ALTER TABLE draw_acme__user DROP child_object1_id, DROP child_object2_id');
    }
}
