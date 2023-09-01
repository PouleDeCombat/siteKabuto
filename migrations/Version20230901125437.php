<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230901125437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_size (id INT AUTO_INCREMENT NOT NULL, taille VARCHAR(25) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adhesions_abonnements ADD CONSTRAINT FK_5A3882B3FF307B6B FOREIGN KEY (adhesions_id) REFERENCES adhesions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adhesions_abonnements ADD CONSTRAINT FK_5A3882B3633E2BBF FOREIGN KEY (abonnements_id) REFERENCES abonnements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kids_abonnements ADD CONSTRAINT FK_4BC26C6DB71E5B2E FOREIGN KEY (kids_id) REFERENCES kids (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kids_abonnements ADD CONSTRAINT FK_4BC26C6D633E2BBF FOREIGN KEY (abonnements_id) REFERENCES abonnements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kids_competitions_kids ADD CONSTRAINT FK_3E097A07F9F224AF FOREIGN KEY (kids_competitions_id) REFERENCES kids_competitions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kids_competitions_kids ADD CONSTRAINT FK_3E097A07B71E5B2E FOREIGN KEY (kids_id) REFERENCES kids (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE orders_abonnements ADD CONSTRAINT FK_343163FDCFFE9AD6 FOREIGN KEY (orders_id) REFERENCES orders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE orders_abonnements ADD CONSTRAINT FK_343163FD633E2BBF FOREIGN KEY (abonnements_id) REFERENCES abonnements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE orders_details ADD CONSTRAINT FK_835379F1CFFE9AD6 FOREIGN KEY (orders_id) REFERENCES orders (id)');
        $this->addSql('ALTER TABLE orders_details ADD CONSTRAINT FK_835379F16C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE products_product_size ADD CONSTRAINT FK_1D0F91656C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE products_product_size ADD CONSTRAINT FK_1D0F91659854B397 FOREIGN KEY (product_size_id) REFERENCES product_size (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE products_product_size DROP FOREIGN KEY FK_1D0F91659854B397');
        $this->addSql('DROP TABLE product_size');
        $this->addSql('ALTER TABLE adhesions_abonnements DROP FOREIGN KEY FK_5A3882B3FF307B6B');
        $this->addSql('ALTER TABLE adhesions_abonnements DROP FOREIGN KEY FK_5A3882B3633E2BBF');
        $this->addSql('ALTER TABLE kids_abonnements DROP FOREIGN KEY FK_4BC26C6DB71E5B2E');
        $this->addSql('ALTER TABLE kids_abonnements DROP FOREIGN KEY FK_4BC26C6D633E2BBF');
        $this->addSql('ALTER TABLE kids_competitions_kids DROP FOREIGN KEY FK_3E097A07F9F224AF');
        $this->addSql('ALTER TABLE kids_competitions_kids DROP FOREIGN KEY FK_3E097A07B71E5B2E');
        $this->addSql('ALTER TABLE orders_abonnements DROP FOREIGN KEY FK_343163FDCFFE9AD6');
        $this->addSql('ALTER TABLE orders_abonnements DROP FOREIGN KEY FK_343163FD633E2BBF');
        $this->addSql('ALTER TABLE orders_details DROP FOREIGN KEY FK_835379F1CFFE9AD6');
        $this->addSql('ALTER TABLE orders_details DROP FOREIGN KEY FK_835379F16C8A81A9');
        $this->addSql('ALTER TABLE products_product_size DROP FOREIGN KEY FK_1D0F91656C8A81A9');
    }
}
