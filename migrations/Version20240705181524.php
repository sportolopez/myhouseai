<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240705181524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE usuario_compras (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, fecha DATETIME NOT NULL, cantidad INT NOT NULL, monto DOUBLE PRECISION NOT NULL, INDEX IDX_4FB44BF0DB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE usuario_compras ADD CONSTRAINT FK_4FB44BF0DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE usuario_compras DROP FOREIGN KEY FK_4FB44BF0DB38439E');
        $this->addSql('DROP TABLE usuario_compras');
    }
}
