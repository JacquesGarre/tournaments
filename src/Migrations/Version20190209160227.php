<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190209160227 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, licence INT NOT NULL, email_adress VARCHAR(255) NOT NULL, points INT DEFAULT NULL, genre VARCHAR(1) NOT NULL, club VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player_board (player_id INT NOT NULL, board_id INT NOT NULL, INDEX IDX_E724500199E6F5DF (player_id), INDEX IDX_E7245001E7EC5785 (board_id), PRIMARY KEY(player_id, board_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player_match (player_id INT NOT NULL, match_id INT NOT NULL, INDEX IDX_C529BE4399E6F5DF (player_id), INDEX IDX_C529BE432ABEACD6 (match_id), PRIMARY KEY(player_id, match_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE player_board ADD CONSTRAINT FK_E724500199E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player_board ADD CONSTRAINT FK_E7245001E7EC5785 FOREIGN KEY (board_id) REFERENCES board (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player_match ADD CONSTRAINT FK_C529BE4399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player_match ADD CONSTRAINT FK_C529BE432ABEACD6 FOREIGN KEY (match_id) REFERENCES `match` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player_board DROP FOREIGN KEY FK_E724500199E6F5DF');
        $this->addSql('ALTER TABLE player_match DROP FOREIGN KEY FK_C529BE4399E6F5DF');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE player_board');
        $this->addSql('DROP TABLE player_match');
    }
}
