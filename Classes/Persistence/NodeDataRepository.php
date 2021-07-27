<?php
declare(strict_types=1);

namespace PunktDe\Analytics\Neos\Persistence;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Doctrine\ORM\Internal\Hydration\IterableResult;
use PunktDe\Analytics\Persistence\AbstractRepository;

class NodeDataRepository extends AbstractRepository
{

    /**
     * @return string
     */
    protected function getDataSourceName(): string
    {
        return 'neos_nodedata';
    }

    public function findAll(): IterableResult
    {
        $statement = sprintf('
SELECT
persistence_object_identifier,
workspace,
identifier,
nodetype,
dimensionvalues,
creationdatetime,
lastmodificationdatetime,
lastpublicationdatetime,
properties
FROM neos_contentrepository_domain_model_nodedata;
');

        $query = $this->dataSource->getEntityManager()->createNativeQuery($statement, $this->buildRsmByQuery($statement));
        return $query->iterate();
    }
}
