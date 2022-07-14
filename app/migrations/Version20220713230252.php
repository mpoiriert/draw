<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220713230252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE draw_acme__user
        ADD
            email_auth_code VARCHAR(255) DEFAULT NULL,
        ADD
            email_auth_code_generated_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'
    ');
    }

    public function down(Schema $schema): void
    {
    }
}
