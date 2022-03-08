<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190310142244 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player_match DROP FOREIGN KEY FK_C529BE432ABEACD6');
        $this->addSql('DROP TABLE `match`');
        $this->addSql('DROP TABLE player_match');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `match` (id INT AUTO_INCREMENT NOT NULL, board_id INT NOT NULL, tournament_id INT NOT NULL, table_number VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, status INT NOT NULL, INDEX IDX_7A5BC50533D1A3E7 (tournament_id), INDEX IDX_7A5BC505E7EC5785 (board_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE player_match (player_id INT NOT NULL, match_id INT NOT NULL, INDEX IDX_C529BE4399E6F5DF (player_id), INDEX IDX_C529BE432ABEACD6 (match_id), PRIMARY KEY(player_id, match_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE `match` ADD CONSTRAINT FK_7A5BC50533D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE `match` ADD CONSTRAINT FK_7A5BC505E7EC5785 FOREIGN KEY (board_id) REFERENCES board (id)');
        $this->addSql('ALTER TABLE player_match ADD CONSTRAINT FK_C529BE432ABEACD6 FOREIGN KEY (match_id) REFERENCES `match` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player_match ADD CONSTRAINT FK_C529BE4399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
    }
}
