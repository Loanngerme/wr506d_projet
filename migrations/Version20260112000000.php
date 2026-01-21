<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add 2FA fields to user table
 */
final class Version20260112000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add two_factor_secret, two_factor_enabled, and two_factor_backup_codes fields to user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD two_factor_secret VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` ADD two_factor_enabled TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE `user` ADD two_factor_backup_codes JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP two_factor_secret');
        $this->addSql('ALTER TABLE `user` DROP two_factor_enabled');
        $this->addSql('ALTER TABLE `user` DROP two_factor_backup_codes');
    }
}
