<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230728115412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users_competitions (users_id INT NOT NULL, competitions_id INT NOT NULL, INDEX IDX_F499603167B3B43D (users_id), INDEX IDX_F499603114B3F5BE (competitions_id), PRIMARY KEY(users_id, competitions_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE users_competitions ADD CONSTRAINT FK_F499603167B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users_competitions ADD CONSTRAINT FK_F499603114B3F5BE FOREIGN KEY (competitions_id) REFERENCES competitions (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE competiteurs');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3BA5A5A989D9B62 ON products (slug)');
        $this->addSql('ALTER TABLE users ADD categorie_poid VARCHAR(255) DEFAULT NULL, ADD ceinture VARCHAR(255) DEFAULT NULL, ADD kimono VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE competiteurs (id INT AUTO_INCREMENT NOT NULL, ceinture VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, categorie_poid JSON NOT NULL, kimono VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE users_competitions DROP FOREIGN KEY FK_F499603167B3B43D');
        $this->addSql('ALTER TABLE users_competitions DROP FOREIGN KEY FK_F499603114B3F5BE');
        $this->addSql('DROP TABLE users_competitions');
        $this->addSql('ALTER TABLE users DROP categorie_poid, DROP ceinture, DROP kimono');
        $this->addSql('DROP INDEX UNIQ_B3BA5A5A989D9B62 ON products');
    }
}
