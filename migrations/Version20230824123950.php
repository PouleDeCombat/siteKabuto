<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230824123950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orders_abonnements (orders_id INT NOT NULL, abonnements_id INT NOT NULL, INDEX IDX_343163FDCFFE9AD6 (orders_id), INDEX IDX_343163FD633E2BBF (abonnements_id), PRIMARY KEY(orders_id, abonnements_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE orders_abonnements ADD CONSTRAINT FK_343163FDCFFE9AD6 FOREIGN KEY (orders_id) REFERENCES orders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE orders_abonnements ADD CONSTRAINT FK_343163FD633E2BBF FOREIGN KEY (abonnements_id) REFERENCES abonnements (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orders_abonnements DROP FOREIGN KEY FK_343163FDCFFE9AD6');
        $this->addSql('ALTER TABLE orders_abonnements DROP FOREIGN KEY FK_343163FD633E2BBF');
        $this->addSql('DROP TABLE orders_abonnements');
    }
}
