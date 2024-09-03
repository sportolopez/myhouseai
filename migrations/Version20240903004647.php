<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240903004647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_enviado ADD CONSTRAINT FK_6EE964422EA791B FOREIGN KEY (inmobiliaria_id) REFERENCES inmobiliaria (id)');
        $this->addSql('ALTER TABLE inmobiliaria CHANGE fecha_ultimo_visto fecha_ultimo_visto DATETIME NOT NULL, CHANGE imagen_ejemplo imagen_ejemplo LONGBLOB NOT NULL, CHANGE ultimo_envio_fecha ultimo_envio_fecha VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE usuario_compras ADD estado VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE variacion DROP FOREIGN KEY FK_AF555CCC763C8AA7');
        $this->addSql('DROP INDEX IDX_AF555CCC763C8AA7 ON variacion');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_enviado DROP FOREIGN KEY FK_6EE964422EA791B');
        $this->addSql('ALTER TABLE variacion ADD CONSTRAINT FK_AF555CCC763C8AA7 FOREIGN KEY (imagen_id) REFERENCES imagen (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_AF555CCC763C8AA7 ON variacion (imagen_id)');
        $this->addSql('ALTER TABLE usuario_compras DROP estado');
        $this->addSql('ALTER TABLE inmobiliaria CHANGE fecha_ultimo_visto fecha_ultimo_visto DATETIME DEFAULT NULL, CHANGE imagen_ejemplo imagen_ejemplo LONGBLOB DEFAULT NULL, CHANGE ultimo_envio_fecha ultimo_envio_fecha DATETIME DEFAULT NULL');
    }
}
