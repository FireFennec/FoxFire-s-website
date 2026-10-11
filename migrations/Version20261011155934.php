<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261011155934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
        ALTER TABLE website_feature
        ALTER COLUMN priority TYPE INTEGER
        USING CASE priority
            WHEN 'Nice to have' THEN 1
            WHEN 'Anytime' THEN 2
            WHEN 'Soon' THEN 3
            WHEN 'High priority' THEN 4
            WHEN 'ASAP' THEN 5
        END
    ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
        ALTER TABLE website_feature
        ALTER COLUMN priority TYPE VARCHAR(255)
        USING CASE priority
            WHEN 1 THEN 'Nice to have'
            WHEN 2 THEN 'Anytime'
            WHEN 3 THEN 'Soon'
            WHEN 4 THEN 'High priority'
            WHEN 5 THEN 'ASAP'
        END
    ");
    }
}
