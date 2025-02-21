<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230228124338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD banned_by_id INT DEFAULT NULL, ADD is_banned TINYINT(1) NOT NULL, ADD banned_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE signup_date signup_date DATETIME DEFAULT \'1970-01-02\' NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649386B8E7 FOREIGN KEY (banned_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649386B8E7 ON user (banned_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649386B8E7');
        $this->addSql('DROP INDEX IDX_8D93D649386B8E7 ON user');
        $this->addSql('ALTER TABLE user DROP banned_by_id, DROP is_banned, DROP banned_date, CHANGE signup_date signup_date DATETIME DEFAULT \'1970-01-02 00:00:00\' NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
