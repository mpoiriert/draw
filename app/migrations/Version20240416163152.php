<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240416163152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cron_job__cron_job (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) NOT NULL,
          active TINYINT(1) DEFAULT 0 NOT NULL,
          command LONGTEXT NOT NULL,
          schedule VARCHAR(255) DEFAULT NULL,
          time_to_live INT DEFAULT 0 NOT NULL,
          priority INT DEFAULT NULL,
          UNIQUE INDEX UNIQ_5D454BF65E237E06 (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cron_job__cron_job_execution (
          id INT AUTO_INCREMENT NOT NULL,
          cron_job_id INT NOT NULL,
          requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          `force` TINYINT(1) DEFAULT 0 NOT NULL,
          execution_started_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          execution_ended_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          execution_delay INT DEFAULT NULL,
          exit_code INT DEFAULT NULL,
          error JSON DEFAULT NULL,
          INDEX IDX_2DD653DD79099ED8 (cron_job_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          cron_job__cron_job_execution
        ADD
          CONSTRAINT FK_2DD653DD79099ED8 FOREIGN KEY (cron_job_id) REFERENCES cron_job__cron_job (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cron_job__cron_job_execution DROP FOREIGN KEY FK_2DD653DD79099ED8');
        $this->addSql('DROP TABLE cron_job__cron_job');
        $this->addSql('DROP TABLE cron_job__cron_job_execution');
    }
}
