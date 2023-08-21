<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230802163535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE kids_competitions (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kids_competitions_kids (kids_competitions_id INT NOT NULL, kids_id INT NOT NULL, INDEX IDX_3E097A07F9F224AF (kids_competitions_id), INDEX IDX_3E097A07B71E5B2E (kids_id), PRIMARY KEY(kids_competitions_id, kids_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kids_competitions_kids ADD CONSTRAINT FK_3E097A07F9F224AF FOREIGN KEY (kids_competitions_id) REFERENCES kids_competitions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kids_competitions_kids ADD CONSTRAINT FK_3E097A07B71E5B2E FOREIGN KEY (kids_id) REFERENCES kids (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kids_competitions_kids DROP FOREIGN KEY FK_3E097A07F9F224AF');
        $this->addSql('ALTER TABLE kids_competitions_kids DROP FOREIGN KEY FK_3E097A07B71E5B2E');
        $this->addSql('DROP TABLE kids_competitions');
        $this->addSql('DROP TABLE kids_competitions_kids');
    }
}
