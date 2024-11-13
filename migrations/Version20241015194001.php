<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241015194001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE opciones ADD id_usuario_id INT DEFAULT NULL, ADD objetivo INT NOT NULL, ADD disponibilidad INT NOT NULL');
        $this->addSql('ALTER TABLE opciones ADD CONSTRAINT FK_6A496B867EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuarios (id)');
        $this->addSql('CREATE INDEX IDX_6A496B867EB2C349 ON opciones (id_usuario_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE opciones DROP FOREIGN KEY FK_6A496B867EB2C349');
        $this->addSql('DROP INDEX IDX_6A496B867EB2C349 ON opciones');
        $this->addSql('ALTER TABLE opciones DROP id_usuario_id, DROP objetivo, DROP disponibilidad');
    }
}
