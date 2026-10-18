<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261018230343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename cooking recipe ingredients column.';
    }

    public function up(Schema $schema): void
    {
        // Rename the existing column without losing its data.
        $this->addSql(
            'ALTER TABLE cooking_recipe RENAME COLUMN ingredience TO ingredients'
        );
    }

    public function down(Schema $schema): void
    {
        // Restore the previous column name.
        $this->addSql(
            'ALTER TABLE cooking_recipe RENAME COLUMN ingredients TO ingredience'
        );
    }
}
