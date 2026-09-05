<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractMigration;

final class Version20260903180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the operation registry that makes a replayed credit a no-op.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE guiziweb_sylius_token_operation (id INT AUTO_INCREMENT NOT NULL, wallet_id INT NOT NULL, idempotency_key VARCHAR(255) NOT NULL, type VARCHAR(32) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6C551913712520F3 (wallet_id), UNIQUE INDEX guiziweb_token_operation_replay_idx (wallet_id, idempotency_key, type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_operation ADD CONSTRAINT FK_6C551913712520F3 FOREIGN KEY (wallet_id) REFERENCES guiziweb_sylius_token_wallet (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guiziweb_sylius_token_operation DROP FOREIGN KEY FK_6C551913712520F3');
        $this->addSql('DROP TABLE guiziweb_sylius_token_operation');
    }
}
