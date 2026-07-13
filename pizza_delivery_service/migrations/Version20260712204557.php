<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260712204557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "delivery" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, total_price NUMERIC(10, 4) DEFAULT NULL, distance NUMERIC(10, 2) DEFAULT NULL, point_of_delivery_id INTEGER NOT NULL, sender_restaurant_id INTEGER DEFAULT NULL, CONSTRAINT FK_3781EC10576F48BF FOREIGN KEY (point_of_delivery_id) REFERENCES point (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3781EC10E67D42CA FOREIGN KEY (sender_restaurant_id) REFERENCES restaurant (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_3781EC10576F48BF ON "delivery" (point_of_delivery_id)');
        $this->addSql('CREATE INDEX IDX_3781EC10E67D42CA ON "delivery" (sender_restaurant_id)');
        $this->addSql('CREATE TABLE point (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, latitude NUMERIC(10, 6) NOT NULL, longitude NUMERIC(10, 6) NOT NULL)');
        $this->addSql('CREATE TABLE restaurant (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, location_id INTEGER NOT NULL, CONSTRAINT FK_EB95123F64D218E FOREIGN KEY (location_id) REFERENCES point (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_EB95123F64D218E ON restaurant (location_id)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE "delivery"');
        $this->addSql('DROP TABLE point');
        $this->addSql('DROP TABLE restaurant');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
