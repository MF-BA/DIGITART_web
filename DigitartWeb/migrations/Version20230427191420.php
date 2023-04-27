<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230427191420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_5F9E962A6B3CA4B ON comments');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5F9E962A6B3CA4B ON comments (id_user)');
        $this->addSql('ALTER TABLE participants ADD id INT AUTO_INCREMENT NOT NULL, CHANGE id_event id_event INT DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_5F9E962A6B3CA4B ON comments');
        $this->addSql('CREATE INDEX UNIQ_5F9E962A6B3CA4B ON comments (id_user)');
        $this->addSql('ALTER TABLE participants MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `PRIMARY` ON participants');
        $this->addSql('ALTER TABLE participants DROP id, CHANGE id_event id_event INT NOT NULL');
        $this->addSql('ALTER TABLE participants ADD PRIMARY KEY (id_event)');
    }
}
