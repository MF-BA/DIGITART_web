<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230421134725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AD52B4B97');
        $this->addSql('DROP INDEX IDX_5F9E962AD52B4B97 ON comments');
        $this->addSql('ALTER TABLE comments ADD event_id INT NOT NULL, DROP id_event');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('CREATE INDEX IDX_5F9E962A71F7E88B ON comments (event_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962A71F7E88B');
        $this->addSql('DROP INDEX IDX_5F9E962A71F7E88B ON comments');
        $this->addSql('ALTER TABLE comments ADD id_event INT DEFAULT NULL, DROP event_id');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AD52B4B97 FOREIGN KEY (id_event) REFERENCES event (id)');
        $this->addSql('CREATE INDEX IDX_5F9E962AD52B4B97 ON comments (id_event)');
    }
}
