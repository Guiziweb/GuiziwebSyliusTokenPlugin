<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260829124103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Materialises the wallet balance, kept in sync with the ledger inside each transaction.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE guiziweb_sylius_token_wallet ADD balance INT DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE guiziweb_sylius_token_wallet w SET balance = (SELECT COALESCE(SUM(b.remaining_amount), 0) FROM guiziweb_sylius_token_batch b WHERE b.wallet_id = w.id AND b.remaining_amount > 0 AND (b.expires_at IS NULL OR b.expires_at > NOW()))');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE guiziweb_sylius_token_wallet DROP balance');
    }
}
