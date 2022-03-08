<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190310144754 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contest (id INT AUTO_INCREMENT NOT NULL, board_id INT NOT NULL, tournament_id INT NOT NULL, name VARCHAR(255) NOT NULL, table_name VARCHAR(255) DEFAULT NULL, status INT NOT NULL, INDEX IDX_1A95CB5E7EC5785 (board_id), INDEX IDX_1A95CB533D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contest_player (contest_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_ADA0AFEF1CD0F0DE (contest_id), INDEX IDX_ADA0AFEF99E6F5DF (player_id), PRIMARY KEY(contest_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contest ADD CONSTRAINT FK_1A95CB5E7EC5785 FOREIGN KEY (board_id) REFERENCES board (id)');
        $this->addSql('ALTER TABLE contest ADD CONSTRAINT FK_1A95CB533D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE contest_player ADD CONSTRAINT FK_ADA0AFEF1CD0F0DE FOREIGN KEY (contest_id) REFERENCES contest (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contest_player ADD CONSTRAINT FK_ADA0AFEF99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contest_player DROP FOREIGN KEY FK_ADA0AFEF1CD0F0DE');
        $this->addSql('DROP TABLE contest');
        $this->addSql('DROP TABLE contest_player');
    }
}
