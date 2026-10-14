<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261013234222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Add new platform columns.
        $this->addSql('ALTER TABLE contact_information ADD steam VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_information ADD steam_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_information ADD battle_net VARCHAR(255) DEFAULT NULL');

        // Update the existing contact without deleting other data.
        $this->addSql("
            UPDATE contact_information
            SET
                steam = 'FoxFire',
                steam_link = 'https://steamcommunity.com/profiles/76561198063270997/',
                battle_net = 'FoxFire#21881'
            WHERE email = 'DanVraspir@protonmail.com'
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_information DROP steam');
        $this->addSql('ALTER TABLE contact_information DROP steam_link');
        $this->addSql('ALTER TABLE contact_information DROP battle_net');
    }
}
