<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230822215918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservations (id INT AUTO_INCREMENT NOT NULL, cours_id INT DEFAULT NULL, INDEX IDX_4DA2397ECF78B0 (cours_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservations_users (reservations_id INT NOT NULL, users_id INT NOT NULL, INDEX IDX_DE575306D9A7F869 (reservations_id), INDEX IDX_DE57530667B3B43D (users_id), PRIMARY KEY(reservations_id, users_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA2397ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE reservations_users ADD CONSTRAINT FK_DE575306D9A7F869 FOREIGN KEY (reservations_id) REFERENCES reservations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservations_users ADD CONSTRAINT FK_DE57530667B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA2397ECF78B0');
        $this->addSql('ALTER TABLE reservations_users DROP FOREIGN KEY FK_DE575306D9A7F869');
        $this->addSql('ALTER TABLE reservations_users DROP FOREIGN KEY FK_DE57530667B3B43D');
        $this->addSql('DROP TABLE reservations');
        $this->addSql('DROP TABLE reservations_users');
    }
}
