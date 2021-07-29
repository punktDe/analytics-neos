<?php
declare(strict_types=1);

namespace PunktDe\Analytics\Neos\Transfer;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Psr\Log\LoggerInterface;
use PunktDe\Analytics\Elasticsearch\ElasticsearchService;
use PunktDe\Analytics\Neos\Elasticsearch\HistoryIndex;
use PunktDe\Analytics\Neos\Elasticsearch\NodeDataIndex;
use PunktDe\Analytics\Neos\Persistence\HistoryRepository;
use PunktDe\Analytics\Neos\Persistence\NodeDataRepository;

class JobRunner
{

    /**
     * @Flow\Inject
     * @var ElasticsearchService
     */
    protected $elasticSearchService;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @Flow\Inject
     * @var HistoryIndex
     */
    protected $historyIndex;

    /**
     * @Flow\Inject
     * @var HistoryRepository
     */
    protected $historyRepository;

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @Flow\Inject
     * @var NodeDataIndex
     */
    protected $nodeDataIndex;

    public function runHistoryTransfer(): void
    {
        $this->elasticSearchService->recreateElasticIndex($this->historyIndex->getName());
        (new HistoryTransferJob('neos_history'))->transferGeneric($this->historyRepository->findAll(), true);
    }

    public function runNodeDataTransfer(): void
    {
        $this->elasticSearchService->recreateElasticIndex($this->nodeDataIndex->getName());
        (new NodeDataTransferJob('neos_nodedata'))->transferGeneric($this->nodeDataRepository->findAll(), true);
    }
}
