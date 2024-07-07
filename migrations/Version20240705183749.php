<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240705183749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE imagen ADD fecha DATETIME NOT NULL, ADD estilo VARCHAR(255) NOT NULL, ADD tipo_habitacion VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE usuario_compras ADD moneda VARCHAR(255) NOT NULL, ADD medio_pago VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE imagen DROP fecha, DROP estilo, DROP tipo_habitacion');
        $this->addSql('ALTER TABLE usuario_compras DROP moneda, DROP medio_pago');
    }
}
