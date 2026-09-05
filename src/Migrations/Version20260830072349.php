<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractPostgreSQLMigration;

final class Version20260830072349 extends AbstractPostgreSQLMigration
{
    public function getDescription(): string
    {
        return 'Creates the token wallet, its batches, its ledger, the operation registry and the price list.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE guiziweb_sylius_token_batch_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE guiziweb_sylius_token_price_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE guiziweb_sylius_token_transaction_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE guiziweb_sylius_token_wallet_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE guiziweb_sylius_token_batch (id INT NOT NULL, wallet_id INT NOT NULL, amount INT NOT NULL, remaining_amount INT NOT NULL, purchase_amount INT DEFAULT NULL, currency_code VARCHAR(3) DEFAULT NULL, origin VARCHAR(32) NOT NULL, acquired_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4B66B9F9712520F3 ON guiziweb_sylius_token_batch (wallet_id)');
        $this->addSql('CREATE INDEX guiziweb_token_batch_availability_idx ON guiziweb_sylius_token_batch (wallet_id, expires_at)');
        $this->addSql('COMMENT ON COLUMN guiziweb_sylius_token_batch.acquired_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN guiziweb_sylius_token_batch.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE guiziweb_sylius_token_price (id INT NOT NULL, code VARCHAR(64) NOT NULL, name VARCHAR(255) NOT NULL, cost INT NOT NULL, enabled BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_79A5C9F477153098 ON guiziweb_sylius_token_price (code)');
        $this->addSql('CREATE TABLE guiziweb_sylius_token_transaction (id INT NOT NULL, wallet_id INT NOT NULL, batch_id INT NOT NULL, order_id INT DEFAULT NULL, amount INT NOT NULL, type VARCHAR(32) NOT NULL, idempotency_key VARCHAR(255) NOT NULL, reason VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_98558A60712520F3 ON guiziweb_sylius_token_transaction (wallet_id)');
        $this->addSql('CREATE INDEX IDX_98558A60F39EBE7A ON guiziweb_sylius_token_transaction (batch_id)');
        $this->addSql('CREATE INDEX IDX_98558A608D9F6D38 ON guiziweb_sylius_token_transaction (order_id)');
        $this->addSql('CREATE UNIQUE INDEX guiziweb_token_transaction_replay_idx ON guiziweb_sylius_token_transaction (idempotency_key, type, batch_id)');
        $this->addSql('COMMENT ON COLUMN guiziweb_sylius_token_transaction.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE guiziweb_sylius_token_wallet (id INT NOT NULL, customer_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3904A3819395C3F3 ON guiziweb_sylius_token_wallet (customer_id)');
        $this->addSql('COMMENT ON COLUMN guiziweb_sylius_token_wallet.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_batch ADD CONSTRAINT FK_4B66B9F9712520F3 FOREIGN KEY (wallet_id) REFERENCES guiziweb_sylius_token_wallet (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction ADD CONSTRAINT FK_98558A60712520F3 FOREIGN KEY (wallet_id) REFERENCES guiziweb_sylius_token_wallet (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction ADD CONSTRAINT FK_98558A60F39EBE7A FOREIGN KEY (batch_id) REFERENCES guiziweb_sylius_token_batch (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction ADD CONSTRAINT FK_98558A608D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_wallet ADD CONSTRAINT FK_3904A3819395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_product_variant ADD token_amount INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product_variant ADD token_validity_months INT DEFAULT NULL');
        $this->addSql('CREATE SEQUENCE guiziweb_sylius_token_operation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE guiziweb_sylius_token_operation (id INT NOT NULL, wallet_id INT NOT NULL, idempotency_key VARCHAR(255) NOT NULL, type VARCHAR(32) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6C551913712520F3 ON guiziweb_sylius_token_operation (wallet_id)');
        $this->addSql('CREATE UNIQUE INDEX guiziweb_token_operation_replay_idx ON guiziweb_sylius_token_operation (wallet_id, idempotency_key, type)');
        $this->addSql('COMMENT ON COLUMN guiziweb_sylius_token_operation.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_operation ADD CONSTRAINT FK_6C551913712520F3 FOREIGN KEY (wallet_id) REFERENCES guiziweb_sylius_token_wallet (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE guiziweb_sylius_token_operation_id_seq CASCADE');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_operation DROP CONSTRAINT FK_6C551913712520F3');
        $this->addSql('DROP TABLE guiziweb_sylius_token_operation');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_batch DROP CONSTRAINT FK_4B66B9F9712520F3');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction DROP CONSTRAINT FK_98558A60712520F3');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction DROP CONSTRAINT FK_98558A60F39EBE7A');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction DROP CONSTRAINT FK_98558A608D9F6D38');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_wallet DROP CONSTRAINT FK_3904A3819395C3F3');
        $this->addSql('DROP SEQUENCE guiziweb_sylius_token_batch_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE guiziweb_sylius_token_price_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE guiziweb_sylius_token_transaction_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE guiziweb_sylius_token_wallet_id_seq CASCADE');
        $this->addSql('DROP TABLE guiziweb_sylius_token_batch');
        $this->addSql('DROP TABLE guiziweb_sylius_token_price');
        $this->addSql('DROP TABLE guiziweb_sylius_token_transaction');
        $this->addSql('DROP TABLE guiziweb_sylius_token_wallet');
        $this->addSql('ALTER TABLE sylius_product_variant DROP token_amount');
        $this->addSql('ALTER TABLE sylius_product_variant DROP token_validity_months');
    }
}
