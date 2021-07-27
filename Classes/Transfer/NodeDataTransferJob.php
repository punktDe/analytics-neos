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
}
