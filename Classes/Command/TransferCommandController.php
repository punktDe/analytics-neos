<?php
declare(strict_types=1);

namespace PunktDe\Analytics\Neos\Command;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use PunktDe\Analytics\Neos\Elasticsearch\NodeDataIndex;
use PunktDe\Analytics\Neos\Persistence\NodeDataRepository;
use PunktDe\Analytics\Neos\Transfer\NodeDataTransferJob;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Psr\Log\LoggerInterface;
use PunktDe\Analytics\Neos\Elasticsearch\HistoryIndex;
use PunktDe\Analytics\Neos\Persistence\HistoryRepository;
use PunktDe\Analytics\Neos\Transfer\HistoryTransferJob;
use PunktDe\Analytics\Elasticsearch\ElasticsearchService;

class TransferCommandController extends CommandController
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

    public function historyCommand(): void
    {
        $this->outputLine('Recreating existing index ' . $this->historyIndex->getName());
        $this->elasticSearchService->recreateElasticIndex($this->historyIndex->getName());
        (new HistoryTransferJob('neos_history'))->transfer($this->historyRepository->findAll());
    }

    public function nodedataCommand(): void
    {
        $this->outputLine('Recreating existing index ' . $this->nodeDataIndex->getName());
        $this->elasticSearchService->recreateElasticIndex($this->nodeDataIndex->getName());
        (new NodeDataTransferJob('neos_nodedata'))->transfer($this->nodeDataRepository->findAll());
    }
}
