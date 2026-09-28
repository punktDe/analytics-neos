<?php
declare(strict_types=1);

namespace PunktDe\Analytics\Neos\Persistence;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query\ResultSetMapping;
use Neos\Flow\Annotations as Flow;
use PunktDe\Analytics\Persistence\AbstractRepository;

class HistoryRepository extends AbstractRepository
{
    private const EVENT_TABLE = 'neos_neos_eventlog_domain_model_event';

    /**
     * Fields added to the event record by the document resolution
     */
    private const RESOLVED_FIELDS = ['title', 'uri', 'country', 'language'];

    /**
     * Time window the events are read from, given as a MySQL INTERVAL expression
     *
     * @Flow\InjectConfiguration(path="neos.history.interval", package="PunktDe.Analytics")
     * @var string
     */
    protected $interval;

    /**
     * @return string
     */
    protected function getDataSourceName(): string
    {
        return 'neos_history';
    }

    public function findAll(): IterableResult
    {
        $statement = $this->buildStatement();

        $query = $this->dataSource->getEntityManager()->createNativeQuery($statement, $this->buildResultSetMapping());
        return $query->iterate();
    }

    /**
     * Selects all events of the configured time window and resolves the title and the uri of the
     * document node the event took place on.
     *
     * The document node is looked up by its identifier and then walked up to the site node to build
     * the uri path. Events whose document node can not be resolved are returned with empty values.
     *
     * @return string
     */
    private function buildStatement(): string
    {
        return sprintf('
WITH RECURSIVE

-- 1. All document node identifiers referenced by the events of the time window
doc_ids AS (
    SELECT DISTINCT documentnodeidentifier AS identifier
    FROM %1$s
    WHERE timestamp >= %2$s
      AND documentnodeidentifier IS NOT NULL
      AND documentnodeidentifier <> \'\'
),

-- 2. Exactly one node data row per (identifier, dimensionshash):
--    prefer live and not removed, otherwise the most recently modified one.
doc_nodes AS (
    SELECT nd.path, nd.parentpath, nd.identifier, nd.dimensionvalues,
           nd.dimensionshash, nd.properties,
           ROW_NUMBER() OVER (
               PARTITION BY nd.identifier, nd.dimensionshash
               ORDER BY (nd.workspace = \'live\') DESC,
                        nd.removed ASC,
                        nd.lastmodificationdatetime DESC
           ) AS rn
    FROM neos_contentrepository_domain_model_nodedata nd
    JOIN doc_ids d ON d.identifier = nd.identifier
),

-- 3. Walk up to /sites, starting at the event nodes themselves
node_selection AS (
    SELECT path, parentpath, identifier, dimensionvalues, dimensionshash,
           CAST(COALESCE(JSON_VALUE(properties, \'$.title\'), \'\') AS CHAR(4000)) AS title,
           CAST(CASE WHEN parentpath = \'/sites\' THEN \'\'
                     ELSE CONCAT(\'/\', COALESCE(JSON_VALUE(properties, \'$.uriPathSegment\'), \'\'))
                END AS CHAR(4000)) AS uri
    FROM doc_nodes
    WHERE rn = 1

    UNION ALL

    SELECT nd.path, nd.parentpath, sel.identifier, sel.dimensionvalues, sel.dimensionshash,
           CONCAT(COALESCE(JSON_VALUE(nd.properties, \'$.title\'), \'\'), \' // \', sel.title),
           CASE WHEN nd.parentpath = \'/sites\' THEN sel.uri   -- site node: no segment
                ELSE CONCAT(\'/\', COALESCE(JSON_VALUE(nd.properties, \'$.uriPathSegment\'), \'\'), sel.uri)
           END
    FROM neos_contentrepository_domain_model_nodedata nd
    JOIN node_selection sel
      ON nd.path = sel.parentpath
     AND nd.dimensionshash = sel.dimensionshash
    WHERE nd.workspace = \'live\'   -- ancestors always from live, user workspaces only hold shadow nodes
),

-- 4. Resolved documents: one row per (identifier, dimensionshash)
resolved AS (
    SELECT identifier,
           dimensionshash,
           title,
           IF(uri = \'\', \'/\',
              CONCAT(\'/\', JSON_UNQUOTE(JSON_EXTRACT(dimensionvalues, \'$.country.0\')),
                     \'_\', JSON_UNQUOTE(JSON_EXTRACT(dimensionvalues, \'$.language.0\')),
                     uri, \'.html\')) AS uri,
           JSON_UNQUOTE(JSON_EXTRACT(dimensionvalues, \'$.country.0\'))  AS country,
           JSON_UNQUOTE(JSON_EXTRACT(dimensionvalues, \'$.language.0\')) AS language
    FROM node_selection
    WHERE parentpath = \'/sites\'
)

SELECT e.*,
       r.title,
       r.uri,
       r.country,
       r.language
FROM %1$s e
LEFT JOIN resolved r
       ON r.identifier     = e.documentnodeidentifier
      AND r.dimensionshash = e.dimensionshash
WHERE e.timestamp >= %2$s
ORDER BY e.timestamp DESC', self::EVENT_TABLE, $this->buildCutoffExpression());
    }

    /**
     * @return string
     * @throws \InvalidArgumentException if the configured interval is not a valid MySQL INTERVAL expression
     */
    private function buildCutoffExpression(): string
    {
        $interval = trim((string)$this->interval);

        if (preg_match('/^\d+ (MICROSECOND|SECOND|MINUTE|HOUR|DAY|WEEK|MONTH|QUARTER|YEAR)$/i', $interval) !== 1) {
            throw new \InvalidArgumentException(sprintf('The configured history interval "%s" is not a valid MySQL INTERVAL expression. Expected something like "1 DAY".', $interval), 1758614400);
        }

        return sprintf('DATE_SUB(NOW(), INTERVAL %s)', $interval);
    }

    /**
     * The event columns are determined from the plain event table instead of the full statement, so
     * that the recursive lookup is not executed a second time and an empty time window does not
     * lead to an EmptyResultException.
     *
     * @return ResultSetMapping
     */
    private function buildResultSetMapping(): ResultSetMapping
    {
        $resultSetMapping = $this->buildRsmByQuery(sprintf('SELECT * FROM %s', self::EVENT_TABLE));

        foreach (self::RESOLVED_FIELDS as $field) {
            $resultSetMapping->addScalarResult($field, $field);
        }

        return $resultSetMapping;
    }
}
