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

class NodeDataRepository extends AbstractRepository
{

    /**
     * @return string
     */
    protected function getDataSourceName(): string
    {
        return 'neos_nodedata';
    }

    public function findAll(int $offset = 0): Statement
    {
        $statement = $this->dataSource->getConnection()->prepare(sprintf('
SELECT
persistence_object_identifier,
workspace,
identifier,
nodetype,
dimensionvalues,
creationdatetime,
lastmodificationdatetime,
lastpublicationdatetime
FROM neos_contentrepository_domain_model_nodedata;
'));

        $statement->execute();

        return $statement;
    }
}
