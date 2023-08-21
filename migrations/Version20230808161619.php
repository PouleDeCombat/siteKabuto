<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230808161619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adhesions (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, kids_id INT DEFAULT NULL, abonnement_id INT DEFAULT NULL, date_debut DATE DEFAULT NULL, date_fin DATE DEFAULT NULL, statut VARCHAR(100) DEFAULT NULL, prix VARCHAR(100) DEFAULT NULL, INDEX IDX_90757B47A76ED395 (user_id), INDEX IDX_90757B47B71E5B2E (kids_id), INDEX IDX_90757B47F1D74413 (abonnement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adhesions ADD CONSTRAINT FK_90757B47A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE adhesions ADD CONSTRAINT FK_90757B47B71E5B2E FOREIGN KEY (kids_id) REFERENCES kids (id)');
        $this->addSql('ALTER TABLE adhesions ADD CONSTRAINT FK_90757B47F1D74413 FOREIGN KEY (abonnement_id) REFERENCES abonnements (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adhesions DROP FOREIGN KEY FK_90757B47A76ED395');
        $this->addSql('ALTER TABLE adhesions DROP FOREIGN KEY FK_90757B47B71E5B2E');
        $this->addSql('ALTER TABLE adhesions DROP FOREIGN KEY FK_90757B47F1D74413');
        $this->addSql('DROP TABLE adhesions');
    }
}
