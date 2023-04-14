<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230409155622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE imageartwork ADD CONSTRAINT FK_9BD9E5962913E0E FOREIGN KEY (id_art) REFERENCES artwork (id_art)');
        $this->addSql('ALTER TABLE auction ADD CONSTRAINT FK_DEE4F59356826C06 FOREIGN KEY (id_artwork) REFERENCES artwork (id_art)');
        $this->addSql('ALTER TABLE bid CHANGE ID ID INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (ID)');
        $this->addSql('ALTER TABLE ticket ADD ticket_edate DATE DEFAULT NULL, DROP ticket_nb, DROP ticket_desc, CHANGE ticket_date ticket_date DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auction DROP FOREIGN KEY FK_DEE4F59356826C06');
        $this->addSql('ALTER TABLE bid MODIFY ID INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON bid');
        $this->addSql('ALTER TABLE bid CHANGE ID ID INT NOT NULL');
        $this->addSql('ALTER TABLE ImageArtwork DROP FOREIGN KEY FK_9BD9E5962913E0E');
        $this->addSql('ALTER TABLE ticket ADD ticket_nb INT NOT NULL, ADD ticket_desc VARCHAR(50) NOT NULL, DROP ticket_edate, CHANGE ticket_date ticket_date DATE DEFAULT \'CURRENT_TIMESTAMP\' NOT NULL');
    }
}
