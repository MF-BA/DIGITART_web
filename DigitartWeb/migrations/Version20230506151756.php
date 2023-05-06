<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230506151756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ImageArtwork (id_img INT AUTO_INCREMENT NOT NULL, id_art INT DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, INDEX fk_art (id_art), PRIMARY KEY(id_img)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_images (id INT AUTO_INCREMENT NOT NULL, users_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_854DA55767B3B43D (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ImageArtwork ADD CONSTRAINT FK_9BD9E5962913E0E FOREIGN KEY (id_art) REFERENCES artwork (id_art)');
        $this->addSql('ALTER TABLE user_images ADD CONSTRAINT FK_854DA55767B3B43D FOREIGN KEY (users_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE artwork ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, DROP image_art');
        $this->addSql('ALTER TABLE artwork ADD CONSTRAINT FK_881FC576276E236A FOREIGN KEY (id_artist) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_881FC576276E236A ON artwork (id_artist)');
        $this->addSql('ALTER TABLE auction ADD added DATETIME NOT NULL, ADD updated DATETIME NOT NULL, ADD deleted DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE bid CHANGE date date DATETIME NOT NULL, CHANGE id_auction id_auction INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bid ADD CONSTRAINT FK_4AF2B3F3795CE3 FOREIGN KEY (id_auction) REFERENCES auction (id_auction)');
        $this->addSql('CREATE INDEX IDX_4AF2B3F3795CE3 ON bid (id_auction)');
        $this->addSql('DROP INDEX UNIQ_5F9E962A6B3CA4B ON comments');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5F9E962A6B3CA4B ON comments (id_user)');
        $this->addSql('ALTER TABLE payment ADD paid TINYINT(1) DEFAULT NULL, ADD createdAt DATETIME DEFAULT NULL, ADD updatedAt DATETIME DEFAULT NULL, CHANGE total_payment total_payment INT NOT NULL');
        $this->addSql('ALTER TABLE room ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE ticket ADD createdAt DATETIME DEFAULT NULL, ADD updatedAt DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD is_verified TINYINT(1) NOT NULL, ADD auth_code VARCHAR(255) DEFAULT NULL, ADD resetToken VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ImageArtwork DROP FOREIGN KEY FK_9BD9E5962913E0E');
        $this->addSql('ALTER TABLE user_images DROP FOREIGN KEY FK_854DA55767B3B43D');
        $this->addSql('DROP TABLE ImageArtwork');
        $this->addSql('DROP TABLE user_images');
        $this->addSql('ALTER TABLE artwork DROP FOREIGN KEY FK_881FC576276E236A');
        $this->addSql('DROP INDEX IDX_881FC576276E236A ON artwork');
        $this->addSql('ALTER TABLE artwork ADD image_art VARCHAR(255) DEFAULT NULL, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE auction DROP added, DROP updated, DROP deleted');
        $this->addSql('ALTER TABLE bid DROP FOREIGN KEY FK_4AF2B3F3795CE3');
        $this->addSql('DROP INDEX IDX_4AF2B3F3795CE3 ON bid');
        $this->addSql('ALTER TABLE bid CHANGE id_auction id_auction INT NOT NULL, CHANGE date date DATE NOT NULL');
        $this->addSql('DROP INDEX UNIQ_5F9E962A6B3CA4B ON comments');
        $this->addSql('CREATE INDEX UNIQ_5F9E962A6B3CA4B ON comments (id_user)');
        $this->addSql('ALTER TABLE payment DROP paid, DROP createdAt, DROP updatedAt, CHANGE total_payment total_payment INT DEFAULT NULL');
        $this->addSql('ALTER TABLE room DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE ticket DROP createdAt, DROP updatedAt');
        $this->addSql('ALTER TABLE users DROP created_at, DROP updated_at, DROP is_verified, DROP auth_code, DROP resetToken');
    }
}
