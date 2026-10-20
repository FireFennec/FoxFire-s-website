<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261020001240 extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Convert personal skill levels from strings to integers';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
        ALTER TABLE personal_skill
        ALTER COLUMN level_of_skill TYPE INT
        USING (
            CASE level_of_skill
                WHEN 'Amateur (1/6)' THEN 1
                WHEN 'Beginner (2/6)' THEN 2
                WHEN 'Advanced (3/6)' THEN 3
                WHEN 'Experienced (4/6)' THEN 4
                WHEN 'Professional (5/6)' THEN 5
                WHEN 'Master (6/6)' THEN 6
                ELSE NULL
            END
        )
    SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
        ALTER TABLE personal_skill
        ALTER COLUMN level_of_skill TYPE VARCHAR(255)
        USING (
            CASE level_of_skill
                WHEN 1 THEN 'Amateur (1/6)'
                WHEN 2 THEN 'Beginner (2/6)'
                WHEN 3 THEN 'Advanced (3/6)'
                WHEN 4 THEN 'Experienced (4/6)'
                WHEN 5 THEN 'Professional (5/6)'
                WHEN 6 THEN 'Master (6/6)'
                ELSE NULL
            END
        )
    SQL);
    }

}
