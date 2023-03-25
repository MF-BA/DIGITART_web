<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230324231536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE auction CHANGE id_artwork id_artwork INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY id_room');
        $this->addSql('ALTER TABLE event CHANGE id_room id_room INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7F9BF4D99 FOREIGN KEY (id_room) REFERENCES room (id_room)');
        $this->addSql('ALTER TABLE participants DROP FOREIGN KEY id_user_const');
        $this->addSql('ALTER TABLE participants DROP FOREIGN KEY id_event_const');
        $this->addSql('ALTER TABLE participants CHANGE id_event id_event INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participants ADD CONSTRAINT FK_71697092D52B4B97 FOREIGN KEY (id_event) REFERENCES event (id)');
        $this->addSql('ALTER TABLE participants ADD CONSTRAINT FK_716970926B3CA4B FOREIGN KEY (id_user) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE auction CHANGE id_artwork id_artwork INT NOT NULL');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7F9BF4D99');
        $this->addSql('ALTER TABLE event CHANGE id_room id_room INT NOT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT id_room FOREIGN KEY (id_room) REFERENCES room (id_room) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participants DROP FOREIGN KEY FK_71697092D52B4B97');
        $this->addSql('ALTER TABLE participants DROP FOREIGN KEY FK_716970926B3CA4B');
        $this->addSql('ALTER TABLE participants CHANGE id_event id_event INT NOT NULL');
        $this->addSql('ALTER TABLE participants ADD CONSTRAINT id_user_const FOREIGN KEY (id_user) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participants ADD CONSTRAINT id_event_const FOREIGN KEY (id_event) REFERENCES event (id) ON UPDATE CASCADE ON DELETE CASCADE');
    }
}
