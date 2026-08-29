<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260829110948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replaces the token price on product variants by a dedicated tariff entity.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE guiziweb_sylius_token_tariff (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(64) NOT NULL, name VARCHAR(255) NOT NULL, cost INT NOT NULL, enabled TINYINT(1) DEFAULT 1 NOT NULL, UNIQUE INDEX UNIQ_D10911E377153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_product_variant DROP token_price');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE guiziweb_sylius_token_tariff');
        $this->addSql('ALTER TABLE sylius_product_variant ADD token_price INT DEFAULT NULL');
    }
}
