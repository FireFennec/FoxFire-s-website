<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261025124855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add GitHub and Spotify profile fields to contact information.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_information ADD github VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_information ADD github_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_information ADD spotify VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_information ADD spotify_link VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_information DROP github');
        $this->addSql('ALTER TABLE contact_information DROP github_link');
        $this->addSql('ALTER TABLE contact_information DROP spotify');
        $this->addSql('ALTER TABLE contact_information DROP spotify_link');
    }
}
