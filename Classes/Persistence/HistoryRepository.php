<?php
declare(strict_types=1);

namespace PunktDe\Analytics\Neos\Persistence;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Kingsquare\Quick\Transaction;
use Neos\Flow\Annotations as Flow;
use Doctrine\DBAL\Driver\Statement;
use PunktDe\Analytics\Persistence\AbstractRepository;
use PunktDe\Analytics\Persistence\DataSource;
use PunktDe\Analytics\Persistence\DataSourceFactory;

class HistoryRepository extends AbstractRepository
{

    /**
     * @return string
     */
    protected function getDataSourceName(): string
    {
        return 'neos_history';
    }

    public function findAll(int $offset = 0): Statement
    {
        $statement = $this->dataSource->getConnection()->prepare(sprintf('SELECT * FROM neos_neos_eventlog_domain_model_event'));

        $statement->execute();

        return $statement;
    }
}
