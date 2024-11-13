<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241015194950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE opciones DROP FOREIGN KEY FK_6A496B867EB2C349');
        $this->addSql('DROP INDEX IDX_6A496B867EB2C349 ON opciones');
        $this->addSql('ALTER TABLE opciones ADD usuario_id INT NOT NULL, DROP id_usuario_id, CHANGE objetivo objetivo INT DEFAULT 1 NOT NULL, CHANGE disponibilidad disponibilidad INT DEFAULT 3 NOT NULL');
        $this->addSql('ALTER TABLE opciones ADD CONSTRAINT FK_6A496B86DB38439E FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6A496B86DB38439E ON opciones (usuario_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE opciones DROP FOREIGN KEY FK_6A496B86DB38439E');
        $this->addSql('DROP INDEX IDX_6A496B86DB38439E ON opciones');
        $this->addSql('ALTER TABLE opciones ADD id_usuario_id INT DEFAULT NULL, DROP usuario_id, CHANGE objetivo objetivo INT NOT NULL, CHANGE disponibilidad disponibilidad INT NOT NULL');
        $this->addSql('ALTER TABLE opciones ADD CONSTRAINT FK_6A496B867EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuarios (id)');
        $this->addSql('CREATE INDEX IDX_6A496B867EB2C349 ON opciones (id_usuario_id)');
    }
}
