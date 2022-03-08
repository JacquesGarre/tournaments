<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190209155614 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE board_user');
        $this->addSql('DROP TABLE match_user');
        $this->addSql('DROP TABLE player');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D99E6F5DF');
        $this->addSql('DROP INDEX IDX_6D28840D99E6F5DF ON payment');
        $this->addSql('ALTER TABLE payment DROP player_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE board_user (board_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_57058F6AE7EC5785 (board_id), INDEX IDX_57058F6AA76ED395 (user_id), PRIMARY KEY(board_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE match_user (match_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_ECFC673B2ABEACD6 (match_id), INDEX IDX_ECFC673BA76ED395 (user_id), PRIMARY KEY(match_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE board_user ADD CONSTRAINT FK_57058F6AA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE board_user ADD CONSTRAINT FK_57058F6AE7EC5785 FOREIGN KEY (board_id) REFERENCES board (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE match_user ADD CONSTRAINT FK_ECFC673B2ABEACD6 FOREIGN KEY (match_id) REFERENCES `match` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE match_user ADD CONSTRAINT FK_ECFC673BA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE payment ADD player_id INT NOT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D99E6F5DF FOREIGN KEY (player_id) REFERENCES fos_user (id)');
        $this->addSql('CREATE INDEX IDX_6D28840D99E6F5DF ON payment (player_id)');
    }
}
