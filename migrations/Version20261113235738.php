<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261113235738 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Add the completion timestamp column.
        $this->addSql(
            'ALTER TABLE website_feature ADD completed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL'
        );

        // Assign the migration timestamp to previously completed features.
        $this->addSql(
            "UPDATE website_feature
         SET completed_at = CURRENT_TIMESTAMP
         WHERE status = 'Completed feature'"
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE website_feature DROP completed_at');
    }
}
