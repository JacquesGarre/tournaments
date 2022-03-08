<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190202150328 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE board (id INT AUTO_INCREMENT NOT NULL, tournament_id INT NOT NULL, name VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, min_points INT DEFAULT NULL, max_points INT DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_58562B4733D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE board_user (board_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_57058F6AE7EC5785 (board_id), INDEX IDX_57058F6AA76ED395 (user_id), PRIMARY KEY(board_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `match` (id INT AUTO_INCREMENT NOT NULL, board_id INT NOT NULL, table_number VARCHAR(255) DEFAULT NULL, status INT NOT NULL, INDEX IDX_7A5BC505E7EC5785 (board_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE match_user (match_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_ECFC673B2ABEACD6 (match_id), INDEX IDX_ECFC673BA76ED395 (user_id), PRIMARY KEY(match_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, board_id INT NOT NULL, player_id INT NOT NULL, value DOUBLE PRECISION NOT NULL, date DATETIME NOT NULL, INDEX IDX_6D28840DE7EC5785 (board_id), INDEX IDX_6D28840D99E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournament_form (id INT AUTO_INCREMENT NOT NULL, tournament_id INT NOT NULL, status INT NOT NULL, expiration_date DATETIME NOT NULL, UNIQUE INDEX UNIQ_65054F7133D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE board ADD CONSTRAINT FK_58562B4733D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE board_user ADD CONSTRAINT FK_57058F6AE7EC5785 FOREIGN KEY (board_id) REFERENCES board (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE board_user ADD CONSTRAINT FK_57058F6AA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `match` ADD CONSTRAINT FK_7A5BC505E7EC5785 FOREIGN KEY (board_id) REFERENCES board (id)');
        $this->addSql('ALTER TABLE match_user ADD CONSTRAINT FK_ECFC673B2ABEACD6 FOREIGN KEY (match_id) REFERENCES `match` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE match_user ADD CONSTRAINT FK_ECFC673BA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DE7EC5785 FOREIGN KEY (board_id) REFERENCES board (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D99E6F5DF FOREIGN KEY (player_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE tournament_form ADD CONSTRAINT FK_65054F7133D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE fos_user ADD firstname VARCHAR(255) NOT NULL, ADD lastname VARCHAR(255) NOT NULL, ADD licence_number VARCHAR(255) DEFAULT NULL, ADD points INT DEFAULT NULL, ADD genre VARCHAR(1) NOT NULL, ADD club VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE board_user DROP FOREIGN KEY FK_57058F6AE7EC5785');
        $this->addSql('ALTER TABLE `match` DROP FOREIGN KEY FK_7A5BC505E7EC5785');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DE7EC5785');
        $this->addSql('ALTER TABLE match_user DROP FOREIGN KEY FK_ECFC673B2ABEACD6');
        $this->addSql('DROP TABLE board');
        $this->addSql('DROP TABLE board_user');
        $this->addSql('DROP TABLE `match`');
        $this->addSql('DROP TABLE match_user');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE tournament_form');
        $this->addSql('ALTER TABLE fos_user DROP firstname, DROP lastname, DROP licence_number, DROP points, DROP genre, DROP club');
    }
}
