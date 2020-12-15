<?php
declare(strict_types=1);

namespace PunktDe\Analytics\Neos\Transfer;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use PunktDe\Analytics\Neos\Elasticsearch\NodeDataIndex;
use PunktDe\Analytics\Neos\Processor\NodeDataProcessor;
use Neos\Flow\Annotations as Flow;
use Doctrine\DBAL\Driver\Statement;
use Neos\Flow\Log\Utility\LogEnvironment;
use PunktDe\Analytics\Transfer\AbstractTransferJob;

class NodeDataTransferJob extends AbstractTransferJob
{
    /**
     * @Flow\Inject
     * @var NodeDataProcessor
     */
    protected $processor;

    /**
     * @Flow\Inject
     * @var NodeDataIndex
     */
    protected $index;

    public function transfer(Statement $statement): void
    {
        $this->logger->info(sprintf('Transferring Data from %s', $this->jobName), LogEnvironment::fromMethodName(__METHOD__));
        $index = $this->index->getName();

        while ($record = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $this->autoBulkIndex($this->processor->convertRecordToDocument($record, $index));
            $this->logStats();
        }

        $this->flushBulkIndex();
        $this->logStats(true);
    }
}
