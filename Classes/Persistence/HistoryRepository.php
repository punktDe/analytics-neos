<?php
declare(strict_types=1);

namespace PunktDe\Analytics\Neos\Persistence;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Doctrine\ORM\Internal\Hydration\IterableResult;
use PunktDe\Analytics\Persistence\AbstractRepository;

class HistoryRepository extends AbstractRepository
{

    /**
     * @return string
     */
    protected function getDataSourceName(): string
    {
        return 'neos_history';
    }

    public function findAll(): IterableResult
    {
        $statement = 'SELECT * FROM neos_neos_eventlog_domain_model_event';

        $query = $this->dataSource->getEntityManager()->createNativeQuery($statement, $this->buildRsmByQuery($statement));
        return $query->iterate();
    }
}
