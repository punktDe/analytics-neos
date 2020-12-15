<?php
declare(strict_types=1);

namespace PunktDe\Analytics\Neos\Transfer;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\DBAL\Driver\Statement;
use Neos\Flow\Log\Utility\LogEnvironment;
use PunktDe\Analytics\Neos\Elasticsearch\HistoryIndex;
use PunktDe\Analytics\Neos\Processor\HistoryProcessor;
use PunktDe\Analytics\Transfer\AbstractTransferJob;

class HistoryTransferJob extends AbstractTransferJob
{
    /**
     * @Flow\Inject
     * @var HistoryProcessor
     */
    protected $processor;

    /**
     * @Flow\Inject
     * @var HistoryIndex
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
