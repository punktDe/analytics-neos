<?php
declare(strict_types=1);

namespace PunktDe\Analytics\Neos\Processor;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Repository\SiteRepository;
use Psr\Log\LoggerInterface;
use PunktDe\Analytics\Processor\ElasticsearchProcessorInterface;

/**
 * @Flow\Scope("singleton")
 */
class HistoryProcessor implements ElasticsearchProcessorInterface
{
    public function convertRecordToDocument(array $record, string $indexPrefix): ?array
    {
        $id = $record['uid'];

        $relevantDataKeys = ['nodeType', 'documentNodeType', 'nodeContextPath', 'documentNodeContextPath', 'documentNodeLabel'];

        $document = [
            'date' => date('c', strtotime($record['timestamp'])),
            'nodeidentifier' => $record['nodeidentifier'],
            'documentnodeidentifier' => $record['documentnodeidentifier'],
            'accountidentifier' => $record['accountidentifier'],
            'eventtype' => $record['eventtype'],
            'dimension' => empty($record['dimension']) ? [] : unserialize($record['dimension']),

            'data' => array_filter(
                json_decode($record['data'], true, 512, JSON_THROW_ON_ERROR),
                static function ($key) use ($relevantDataKeys) {
                    return in_array($key, $relevantDataKeys, true);
                },
                ARRAY_FILTER_USE_KEY
            ),

            'node_count_change' => 0,
            'hour_of_day' => date('H', strtotime($record['timestamp'])),
            'day_of_week_label' => date('l', strtotime($record['timestamp'])),
            'day_of_week_numeric' => date('w', strtotime($record['timestamp'])),
        ];

        if (isset($document['data']['nodeContextPath'])) {
            $document['site'] = explode('/', $document['data']['nodeContextPath'])[1] ?? 'unknown';
        } else {
            $document['site'] = 'unknown';
        }

        switch ($record['eventtype']) {
            case 'Node.Copy':
            case 'Node.Added':
            case 'Node.Adopt':
                $document['node_count_change'] = 1;
                break;
            case 'Node.Removed':
                $document['node_count_change'] = -1;
                break;
        }

        return [
            'index' => $indexPrefix,
            'id' => $id,
            'body' => $document
        ];
    }
}
