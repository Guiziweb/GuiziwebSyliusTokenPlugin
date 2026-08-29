<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260828185115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the token wallet, its batches and its ledger, and the token amount granted by a product variant.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE guiziweb_sylius_token_batch (id INT AUTO_INCREMENT NOT NULL, wallet_id INT NOT NULL, amount INT NOT NULL, remaining_amount INT NOT NULL, purchase_amount INT DEFAULT NULL, currency_code VARCHAR(3) DEFAULT NULL, origin VARCHAR(32) NOT NULL, acquired_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4B66B9F9712520F3 (wallet_id), INDEX guiziweb_token_batch_availability_idx (wallet_id, expires_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guiziweb_sylius_token_transaction (id INT AUTO_INCREMENT NOT NULL, wallet_id INT NOT NULL, batch_id INT NOT NULL, order_id INT DEFAULT NULL, amount INT NOT NULL, type VARCHAR(32) NOT NULL, idempotency_key VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_98558A60712520F3 (wallet_id), INDEX IDX_98558A60F39EBE7A (batch_id), INDEX IDX_98558A608D9F6D38 (order_id), INDEX guiziweb_token_transaction_history_idx (wallet_id, created_at), UNIQUE INDEX guiziweb_token_transaction_replay_idx (idempotency_key, type, batch_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guiziweb_sylius_token_wallet (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_3904A3819395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_batch ADD CONSTRAINT FK_4B66B9F9712520F3 FOREIGN KEY (wallet_id) REFERENCES guiziweb_sylius_token_wallet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction ADD CONSTRAINT FK_98558A60712520F3 FOREIGN KEY (wallet_id) REFERENCES guiziweb_sylius_token_wallet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction ADD CONSTRAINT FK_98558A60F39EBE7A FOREIGN KEY (batch_id) REFERENCES guiziweb_sylius_token_batch (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction ADD CONSTRAINT FK_98558A608D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_wallet ADD CONSTRAINT FK_3904A3819395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_variant ADD token_amount INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE guiziweb_sylius_token_batch DROP FOREIGN KEY FK_4B66B9F9712520F3');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction DROP FOREIGN KEY FK_98558A60712520F3');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction DROP FOREIGN KEY FK_98558A60F39EBE7A');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction DROP FOREIGN KEY FK_98558A608D9F6D38');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_wallet DROP FOREIGN KEY FK_3904A3819395C3F3');
        $this->addSql('DROP TABLE guiziweb_sylius_token_batch');
        $this->addSql('DROP TABLE guiziweb_sylius_token_transaction');
        $this->addSql('DROP TABLE guiziweb_sylius_token_wallet');
        $this->addSql('ALTER TABLE sylius_product_variant DROP token_amount');
    }
}
