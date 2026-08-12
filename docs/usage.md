# Usage Reference

The README's Usage section is deliberately minimal — one short, runnable example per class, using the bundled `Adapter\InMemoryArrayAdapter`.
This file holds the one example that doesn't fit there: the intended shape of a real repository against a real datasource.

## A repository, shaped for a real datasource

Filtering/sorting stays entirely in your own `Query` class and repository methods; the library never sees either.
`Sort` (a bare `ASC`/`DESC` enum) and any `EnumValues`-style helper for serializing backed enums are your own project's concern too — not exported by this library, since it never inspects or acts on them.
`OrmSimpleAdapter` below ships in the (not yet published) `search-query-doctrine` package, which implements `PageAdapter`/`SliceAdapter`/`CountableAdapter`/`IdentifiableAdapter`/`HeadableAdapter`/`AllAdapter` over a Doctrine `QueryBuilder`.

```php
<?php

declare(strict_types=1);

namespace App\Domain\Request;

use App\Application\Query\Sort;
use Doctrine\ORM\QueryBuilder;
use PimBay\SearchQuery\Page\PageAdapter;
use PimBay\SearchQuery\Page\PageAssembler;
use PimBay\SearchQuery\Page\PageResult;

final class RequestsQuery
{
    /**
     * @param string[]|null $siteZones
     * @param string[]|null $siteSites
     * @param string[]|null $siteLocales
     * @param array{createdAt?: Sort} $sort
     */
    public function __construct(
        public ?array $siteZones = null,
        public ?array $siteSites = null,
        public ?array $siteLocales = null,
        public array $sort = [],
    ) {
    }
}

final class RequestsSearchRepository
{
    public function __construct(
        private readonly RequestRepository $repository,
    ) {
    }

    public function paginate(RequestsQuery $query, int $page, int $size): PageResult
    {
        $qb = $this->createQueryBuilder($query);
        
        return (new PageAssembler())->paginateOrThrow(new OrmSimpleAdapter($qb), $page, $size);
    }

    /**
     * @return array<int, array{zone: string, count: int}>
     */
    public function aggregateZone(RequestsQuery $query): array
    {
        return $this->createQueryBuilder($query)
            ->select('r.site.zone AS zone', 'COUNT(r.id) AS count')
            ->groupBy('r.site.zone')
            ->getQuery()
            ->getArrayResult();
    }

    private function createQueryBuilder(RequestsQuery $query): QueryBuilder
    {
        $qb = $this->repository->createQueryBuilder('r');

        $this->filter($qb, $query);
        $this->order($qb, $query);

        return $qb;
    }

    private function filter(QueryBuilder $qb, RequestsQuery $query): void
    {
        if ($query->siteZones) {
            $qb->andWhere('r.siteLanguage.zone IN (:siteZones)')->setParameter('siteZones', $query->siteZones);
        }
        if ($query->siteSites) {
            $qb->andWhere('r.siteLanguage.site IN (:siteSites)')->setParameter('siteSites', $query->siteSites);
        }
        if ($query->siteLocales) {
            $qb->andWhere('r.siteLanguage.locale IN (:siteLocales)')->setParameter('siteLocales', $query->siteLocales);
        }
    }

    private function order(QueryBuilder $qb, RequestsQuery $query): void
    {
        foreach ($query->sort as $field => $order) {
            if ($field === 'createdAt') {
                $qb->addOrderBy('r.createdAt', $order->value);
            }
        }

        $qb->addOrderBy('r.id', 'DESC');
    }
}
```
