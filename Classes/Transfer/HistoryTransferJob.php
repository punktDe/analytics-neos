<?php
declare(strict_types=1);

namespace PunktDe\Analytics\Neos\Transfer;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
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
}
