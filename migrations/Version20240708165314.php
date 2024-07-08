<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240708165314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE variacion (id INT AUTO_INCREMENT NOT NULL, imagen_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_AF555CCC763C8AA7 (imagen_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE variacion ADD CONSTRAINT FK_AF555CCC763C8AA7 FOREIGN KEY (imagen_id) REFERENCES imagen (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE variacion DROP FOREIGN KEY FK_AF555CCC763C8AA7');
        $this->addSql('DROP TABLE variacion');
    }
}
