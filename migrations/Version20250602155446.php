<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250602155446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE game (id SERIAL NOT NULL, stage INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE player (id SERIAL NOT NULL, game_id INT NOT NULL, key VARCHAR(2) NOT NULL, team VARCHAR(10) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_98197A65E48FD905 ON player (game_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE point (id SERIAL NOT NULL, player_id INT NOT NULL, stage INT NOT NULL, value INT NOT NULL, team VARCHAR(10) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B7A5F32499E6F5DF ON point (player_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player ADD CONSTRAINT FK_98197A65E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE point ADD CONSTRAINT FK_B7A5F32499E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player DROP CONSTRAINT FK_98197A65E48FD905
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE point DROP CONSTRAINT FK_B7A5F32499E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE game
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE point
        SQL);
    }
}
