<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230615111619 extends AbstractMigration
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
          on_delete_restrict_id INT DEFAULT NULL,
        ADD
          on_delete_cascade_id INT DEFAULT NULL,
        ADD
          on_delete_set_null_id INT DEFAULT NULL,
        ADD
          on_delete_cascade_config_overridden_id INT DEFAULT NULL,
        ADD
          on_delete_cascade_attribute_overridden_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          draw_acme__user
        ADD
          CONSTRAINT FK_5E86F9A7E864B41F FOREIGN KEY (on_delete_restrict_id) REFERENCES draw_acme__base_object (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE
          draw_acme__user
        ADD
          CONSTRAINT FK_5E86F9A77FFFEA0E FOREIGN KEY (on_delete_cascade_id) REFERENCES draw_acme__base_object (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          draw_acme__user
        ADD
          CONSTRAINT FK_5E86F9A72A00A4ED FOREIGN KEY (on_delete_set_null_id) REFERENCES draw_acme__base_object (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          draw_acme__user
        ADD
          CONSTRAINT FK_5E86F9A79E145C6D FOREIGN KEY (
            on_delete_cascade_config_overridden_id
          ) REFERENCES draw_acme__base_object (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          draw_acme__user
        ADD
          CONSTRAINT FK_5E86F9A79A3CF4B7 FOREIGN KEY (
            on_delete_cascade_attribute_overridden_id
          ) REFERENCES draw_acme__base_object (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_5E86F9A7E864B41F ON draw_acme__user (on_delete_restrict_id)');
        $this->addSql('CREATE INDEX IDX_5E86F9A77FFFEA0E ON draw_acme__user (on_delete_cascade_id)');
        $this->addSql('CREATE INDEX IDX_5E86F9A72A00A4ED ON draw_acme__user (on_delete_set_null_id)');
        $this->addSql('CREATE INDEX IDX_5E86F9A79E145C6D ON draw_acme__user (on_delete_cascade_config_overridden_id)');
        $this->addSql('CREATE INDEX IDX_5E86F9A79A3CF4B7 ON draw_acme__user (
          on_delete_cascade_attribute_overridden_id
        )');
    }

    public function down(Schema $schema): void
    {
    }
}
