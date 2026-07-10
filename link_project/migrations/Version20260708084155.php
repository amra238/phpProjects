<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260708084155 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__link AS SELECT id, full_url, short_code, created_at, last_used_at, visit_count, is_one_time, expiration_date, user_id FROM link');
        $this->addSql('DROP TABLE link');
        $this->addSql('CREATE TABLE link (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, full_url VARCHAR(2048) NOT NULL, short_code VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, last_used_at DATETIME DEFAULT NULL, visit_count INTEGER NOT NULL, is_one_time BOOLEAN NOT NULL, expiration_date DATETIME DEFAULT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_36AC99F1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO link (id, full_url, short_code, created_at, last_used_at, visit_count, is_one_time, expiration_date, user_id) SELECT id, full_url, short_code, created_at, last_used_at, visit_count, is_one_time, expiration_date, user_id FROM __temp__link');
        $this->addSql('DROP TABLE __temp__link');
        $this->addSql('CREATE INDEX IDX_36AC99F1A76ED395 ON link (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_36AC99F117D2FE0D ON link (short_code)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__link AS SELECT id, full_url, short_code, is_one_time, expiration_date, created_at, last_used_at, visit_count, user_id FROM link');
        $this->addSql('DROP TABLE link');
        $this->addSql('CREATE TABLE link (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, full_url VARCHAR(2048) NOT NULL, short_code VARCHAR(255) NOT NULL, is_one_time BOOLEAN NOT NULL, expiration_date VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, last_used_at DATETIME DEFAULT NULL, visit_count INTEGER NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_36AC99F1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO link (id, full_url, short_code, is_one_time, expiration_date, created_at, last_used_at, visit_count, user_id) SELECT id, full_url, short_code, is_one_time, expiration_date, created_at, last_used_at, visit_count, user_id FROM __temp__link');
        $this->addSql('DROP TABLE __temp__link');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_36AC99F117D2FE0D ON link (short_code)');
        $this->addSql('CREATE INDEX IDX_36AC99F1A76ED395 ON link (user_id)');
    }
}
