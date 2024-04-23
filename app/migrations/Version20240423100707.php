<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240423100707 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cron_job__cron_job_execution ADD state VARCHAR(20) DEFAULT \'requested\' NOT NULL');
        $this->addSql('CREATE INDEX state ON cron_job__cron_job_execution (state)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX state ON cron_job__cron_job_execution');
        $this->addSql('ALTER TABLE cron_job__cron_job_execution DROP state');
    }
}
