<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Core\Model\ProductVariantInterface;

final readonly class TokenProductRepository
{
    /** @param class-string<ProductVariantInterface> $productVariantClass */
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private string $productVariantClass,
    ) {
    }

    public function createListQueryBuilder(string $localeCode): QueryBuilder
    {
        return $this->createQueryBuilder($localeCode)
            ->andWhere('o.tokenAmount IS NOT NULL OR o.tokenPrice IS NOT NULL')
        ;
    }

    public function createPackListQueryBuilder(string $localeCode): QueryBuilder
    {
        return $this->createQueryBuilder($localeCode)->andWhere('o.tokenAmount IS NOT NULL');
    }

    public function createConsumableListQueryBuilder(string $localeCode): QueryBuilder
    {
        return $this->createQueryBuilder($localeCode)->andWhere('o.tokenPrice IS NOT NULL');
    }

    private function createQueryBuilder(string $localeCode): QueryBuilder
    {
        $manager = $this->managerRegistry->getManagerForClass($this->productVariantClass);

        if (!$manager instanceof EntityManagerInterface) {
            throw new \RuntimeException(sprintf('No entity manager found for "%s".', $this->productVariantClass));
        }

        $queryBuilder = $manager->createQueryBuilder()
            ->select('o')
            ->from($this->productVariantClass, 'o')
            ->innerJoin('o.product', 'product')
            ->leftJoin('product.translations', 'translation', 'WITH', 'translation.locale = :localeCode')
            ->setParameter('localeCode', $localeCode)
        ;

        return $queryBuilder;
    }
}
