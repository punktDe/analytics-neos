<?php
declare(strict_types=1);

namespace PunktDe\Analytics\Neos\Processor;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Psr\Log\LoggerInterface;
use PunktDe\Analytics\Processor\ElasticsearchProcessorInterface;

/**
 * @Flow\Scope("singleton")
 */
class NodeDataProcessor implements ElasticsearchProcessorInterface
{

    public function convertRecordToDocument(array $record, string $indexName): ?array
    {
        $id = $record['persistence_object_identifier'];

        $document = [
            'creationdatetime' => empty($record['creationdatetime']) ? null : date('c', strtotime($record['creationdatetime'])),
            'lastmodificationdatetime' => empty($record['lastmodificationdatetime']) ? null : date('c', strtotime($record['lastmodificationdatetime'])),
            'lastpublicationdatetime' => empty($record['lastpublicationdatetime']) ? null : date('c', strtotime($record['lastpublicationdatetime'])),
            'nodeidentifier' => $record['identifier'],
            'workspacename' => $record['workspace'],
            'nodetype' => $record['nodetype'],
            'dimensionvalues' => json_decode($record['dimensionvalues'], true, 512, JSON_THROW_ON_ERROR),
        ];

        return [
            'index' => $indexName,
            'id' => $id,
            'body' => $document
        ];
    }
}
