<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240708170746 extends AbstractMigration
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
        $this->addSql('ALTER TABLE imagen ADD img_origen LONGBLOB NOT NULL, DROP img_generada, DROP data');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE variacion DROP FOREIGN KEY FK_AF555CCC763C8AA7');
        $this->addSql('DROP TABLE variacion');
        $this->addSql('ALTER TABLE imagen ADD img_generada LONGBLOB DEFAULT NULL, ADD data VARCHAR(100) DEFAULT NULL, DROP img_origen');
    }
}
