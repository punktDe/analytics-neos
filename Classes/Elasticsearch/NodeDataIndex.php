<?php
declare(strict_types=1);

namespace PunktDe\Analytics\Neos\Elasticsearch;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use PunktDe\Analytics\Collections\EmployeeCollection;
use PunktDe\Analytics\Dto\Employee;
use PunktDe\Analytics\Elasticsearch\AbstractIndex;

/**
 * @method  delete(array $array)
 * @method  index(array $params)
 * @method  search(array $params)
 * @method  get(array $params)
 * @method  reindex(array $params)
 * @method  bulk(array $params)
 */
class NodeDataIndex extends AbstractIndex
{
    protected $indexName = 'neos_nodedata';
}
