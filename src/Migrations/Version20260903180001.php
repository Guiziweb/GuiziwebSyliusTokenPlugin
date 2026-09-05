<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractPostgreSQLMigration;

final class Version20260903180001 extends AbstractPostgreSQLMigration
{
    public function getDescription(): string
    {
        return 'Adds the operation registry that makes a replayed credit a no-op.';
    }

    public function up(Schema $schema): void
    {
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
    }
}
