<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230801124320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kids ADD CONSTRAINT FK_42F8D194A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_42F8D194A76ED395 ON kids (user_id)');
        $this->addSql('ALTER TABLE orders ADD total INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orders DROP total');
        $this->addSql('ALTER TABLE kids DROP FOREIGN KEY FK_42F8D194A76ED395');
        $this->addSql('DROP INDEX IDX_42F8D194A76ED395 ON kids');
    }
}
