<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230401214156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artwork (id_art INT AUTO_INCREMENT NOT NULL, id_artist INT DEFAULT NULL, id_room INT DEFAULT NULL, artwork_name VARCHAR(255) NOT NULL, artist_name VARCHAR(255) DEFAULT NULL, date_art DATE NOT NULL, description TEXT DEFAULT NULL, image_art VARCHAR(255) DEFAULT NULL, INDEX IDX_881FC576276E236A (id_artist), INDEX fk_art (id_room), PRIMARY KEY(id_art)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE auction (id_auction INT AUTO_INCREMENT NOT NULL, id_artwork INT DEFAULT NULL, starting_price INT NOT NULL, increment INT DEFAULT 10 NOT NULL, ending_date DATE NOT NULL, description TEXT NOT NULL, state VARCHAR(10) DEFAULT NULL, INDEX fk_artwork (id_artwork), PRIMARY KEY(id_auction)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bid (id_auction INT DEFAULT NULL, ID INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, offer INT NOT NULL, id_user INT NOT NULL, INDEX IDX_4AF2B3F3795CE3 (id_auction), PRIMARY KEY(ID)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, id_room INT DEFAULT NULL, event_name VARCHAR(255) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, nb_participants VARCHAR(255) NOT NULL, detail VARCHAR(255) NOT NULL, start_time INT NOT NULL, image VARCHAR(255) DEFAULT NULL, INDEX id_room (id_room), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participants (id_user INT NOT NULL, id_event INT DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, adress VARCHAR(255) NOT NULL, gender VARCHAR(255) NOT NULL, INDEX id_event_const (id_event), PRIMARY KEY(id_user)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (payment_id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, purchase_date DATE NOT NULL, nb_adult INT DEFAULT NULL, nb_teenager INT DEFAULT NULL, nb_student INT DEFAULT NULL, total_payment INT DEFAULT NULL, INDEX pk (user_id), PRIMARY KEY(payment_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room (id_room INT AUTO_INCREMENT NOT NULL, name_room VARCHAR(255) NOT NULL, area INT NOT NULL, state VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id_room)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket (ticket_id INT AUTO_INCREMENT NOT NULL, ticket_date DATE DEFAULT NULL, ticket_edate DATE DEFAULT NULL, price INT NOT NULL, ticket_type VARCHAR(50) NOT NULL, PRIMARY KEY(ticket_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, cin INT DEFAULT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, phone_num INT DEFAULT NULL, birth_date DATE DEFAULT NULL, gender VARCHAR(255) DEFAULT NULL, role VARCHAR(255) DEFAULT \'Subscriber\', status VARCHAR(255) DEFAULT \'unblocked\' NOT NULL, image VARCHAR(255) DEFAULT NULL, secretcode VARCHAR(255) DEFAULT NULL, roles LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE artwork ADD CONSTRAINT FK_881FC576276E236A FOREIGN KEY (id_artist) REFERENCES users (id)');
        $this->addSql('ALTER TABLE artwork ADD CONSTRAINT FK_881FC576F9BF4D99 FOREIGN KEY (id_room) REFERENCES room (id_room)');
        $this->addSql('ALTER TABLE auction ADD CONSTRAINT FK_DEE4F59356826C06 FOREIGN KEY (id_artwork) REFERENCES artwork (id_art)');
        $this->addSql('ALTER TABLE bid ADD CONSTRAINT FK_4AF2B3F3795CE3 FOREIGN KEY (id_auction) REFERENCES auction (id_auction)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7F9BF4D99 FOREIGN KEY (id_room) REFERENCES room (id_room)');
        $this->addSql('ALTER TABLE participants ADD CONSTRAINT FK_71697092D52B4B97 FOREIGN KEY (id_event) REFERENCES event (id)');
        $this->addSql('ALTER TABLE participants ADD CONSTRAINT FK_716970926B3CA4B FOREIGN KEY (id_user) REFERENCES users (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artwork DROP FOREIGN KEY FK_881FC576276E236A');
        $this->addSql('ALTER TABLE artwork DROP FOREIGN KEY FK_881FC576F9BF4D99');
        $this->addSql('ALTER TABLE auction DROP FOREIGN KEY FK_DEE4F59356826C06');
        $this->addSql('ALTER TABLE bid DROP FOREIGN KEY FK_4AF2B3F3795CE3');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7F9BF4D99');
        $this->addSql('ALTER TABLE participants DROP FOREIGN KEY FK_71697092D52B4B97');
        $this->addSql('ALTER TABLE participants DROP FOREIGN KEY FK_716970926B3CA4B');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DA76ED395');
        $this->addSql('DROP TABLE artwork');
        $this->addSql('DROP TABLE auction');
        $this->addSql('DROP TABLE bid');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE participants');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE ticket');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
