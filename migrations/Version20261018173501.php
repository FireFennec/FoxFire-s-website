<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261018173501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change cooking recipe ingredients storage to JSON.';
    }

    public function up(Schema $schema): void
    {
        // Change the ingredients column from TEXT to JSON.
        $this->addSql(
            'ALTER TABLE cooking_recipe ALTER COLUMN ingredience TYPE JSON USING ingredience::json'
        );
    }

    public function down(Schema $schema): void
    {
        // Reverting to TEXT does not restore SIMPLE_ARRAY semantics.
        $this->addSql(
            'ALTER TABLE cooking_recipe ALTER COLUMN ingredience TYPE TEXT USING ingredience::text'
        );
    }
}
