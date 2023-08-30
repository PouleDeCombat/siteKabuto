<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230829170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE kids_abonnements (kids_id INT NOT NULL, abonnements_id INT NOT NULL, INDEX IDX_4BC26C6DB71E5B2E (kids_id), INDEX IDX_4BC26C6D633E2BBF (abonnements_id), PRIMARY KEY(kids_id, abonnements_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kids_abonnements ADD CONSTRAINT FK_4BC26C6DB71E5B2E FOREIGN KEY (kids_id) REFERENCES kids (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kids_abonnements ADD CONSTRAINT FK_4BC26C6D633E2BBF FOREIGN KEY (abonnements_id) REFERENCES abonnements (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kids_abonnements DROP FOREIGN KEY FK_4BC26C6DB71E5B2E');
        $this->addSql('ALTER TABLE kids_abonnements DROP FOREIGN KEY FK_4BC26C6D633E2BBF');
        $this->addSql('DROP TABLE kids_abonnements');
    }
}
