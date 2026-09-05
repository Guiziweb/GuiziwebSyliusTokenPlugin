<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractMigration;

final class Version20260830063158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the token wallet, its batches, its ledger and the token price list.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE guiziweb_sylius_token_batch (id INT AUTO_INCREMENT NOT NULL, wallet_id INT NOT NULL, amount INT NOT NULL, remaining_amount INT NOT NULL, purchase_amount INT DEFAULT NULL, currency_code VARCHAR(3) DEFAULT NULL, origin VARCHAR(32) NOT NULL, acquired_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4B66B9F9712520F3 (wallet_id), INDEX guiziweb_token_batch_availability_idx (wallet_id, expires_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guiziweb_sylius_token_price (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(64) NOT NULL, name VARCHAR(255) NOT NULL, cost INT NOT NULL, enabled TINYINT(1) DEFAULT 1 NOT NULL, UNIQUE INDEX UNIQ_79A5C9F477153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guiziweb_sylius_token_transaction (id INT AUTO_INCREMENT NOT NULL, wallet_id INT NOT NULL, batch_id INT NOT NULL, order_id INT DEFAULT NULL, amount INT NOT NULL, type VARCHAR(32) NOT NULL, idempotency_key VARCHAR(255) NOT NULL, reason VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_98558A60712520F3 (wallet_id), INDEX IDX_98558A60F39EBE7A (batch_id), INDEX IDX_98558A608D9F6D38 (order_id), UNIQUE INDEX guiziweb_token_transaction_replay_idx (idempotency_key, type, batch_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guiziweb_sylius_token_wallet (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_3904A3819395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_batch ADD CONSTRAINT FK_4B66B9F9712520F3 FOREIGN KEY (wallet_id) REFERENCES guiziweb_sylius_token_wallet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction ADD CONSTRAINT FK_98558A60712520F3 FOREIGN KEY (wallet_id) REFERENCES guiziweb_sylius_token_wallet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction ADD CONSTRAINT FK_98558A60F39EBE7A FOREIGN KEY (batch_id) REFERENCES guiziweb_sylius_token_batch (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction ADD CONSTRAINT FK_98558A608D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_wallet ADD CONSTRAINT FK_3904A3819395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sylius_product_variant ADD token_amount INT DEFAULT NULL, ADD token_validity_months INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guiziweb_sylius_token_batch DROP FOREIGN KEY FK_4B66B9F9712520F3');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction DROP FOREIGN KEY FK_98558A60712520F3');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction DROP FOREIGN KEY FK_98558A60F39EBE7A');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_transaction DROP FOREIGN KEY FK_98558A608D9F6D38');
        $this->addSql('ALTER TABLE guiziweb_sylius_token_wallet DROP FOREIGN KEY FK_3904A3819395C3F3');
        $this->addSql('DROP TABLE guiziweb_sylius_token_batch');
        $this->addSql('DROP TABLE guiziweb_sylius_token_price');
        $this->addSql('DROP TABLE guiziweb_sylius_token_transaction');
        $this->addSql('DROP TABLE guiziweb_sylius_token_wallet');
        $this->addSql('ALTER TABLE sylius_product_variant DROP token_amount, DROP token_validity_months');
    }
}
