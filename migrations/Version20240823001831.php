<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240823001831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE email_enviado (id INT AUTO_INCREMENT NOT NULL, inmobiliaria_id INT NOT NULL, email_version VARCHAR(255) NOT NULL, fecha DATETIME NOT NULL, visto TINYINT(1) NOT NULL, visto_fecha DATETIME NOT NULL, INDEX IDX_6EE964422EA791B (inmobiliaria_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE email_enviado ADD CONSTRAINT FK_6EE964422EA791B FOREIGN KEY (inmobiliaria_id) REFERENCES inmobiliaria (id)');
        $this->addSql('ALTER TABLE inmobiliaria CHANGE fecha_ultimo_visto fecha_ultimo_visto DATETIME NOT NULL, CHANGE imagen_ejemplo imagen_ejemplo LONGBLOB NOT NULL, CHANGE ultimo_envio_fecha ultimo_envio_fecha VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_enviado DROP FOREIGN KEY FK_6EE964422EA791B');
        $this->addSql('DROP TABLE email_enviado');
        $this->addSql('ALTER TABLE inmobiliaria CHANGE fecha_ultimo_visto fecha_ultimo_visto DATETIME DEFAULT NULL, CHANGE imagen_ejemplo imagen_ejemplo LONGBLOB DEFAULT NULL, CHANGE ultimo_envio_fecha ultimo_envio_fecha DATETIME DEFAULT NULL');
    }
}
