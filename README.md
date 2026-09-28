# PunktDe.Analytics.Neos

Transfers Neos content and editing history into Elasticsearch for analytics (e.g. Kibana dashboards).
Built on `punktde/analytics` (>= 2.0).

## What it indexes

| Index           | Source                                   | Content                                                                                                          |
|-----------------|------------------------------------------|------------------------------------------------------------------------------------------------------------------|
| `neos_history`  | `neos_neos_eventlog_domain_model_event`  | Editing events of the configured time window, enriched with site, document title, URI, dimensions, time of day/week and node count change |
| `neos_nodedata` | `neos_contentrepository_domain_model_nodedata` | All nodes with node type, workspace, dimensions, timestamps and outgoing links                          |

Each run drops and fully rebuilds the target index.

## Requirements

- MySQL 8.0.21+ (the history query uses recursive CTEs and `JSON_VALUE`)
- Elasticsearch

## Configuration

The database connection is read from the `MYSQL_DATABASE`, `MYSQL_USERNAME`, `MYSQL_PASSWORD` and `MYSQL_HOST` environment variables. Adjust the Elasticsearch server and the history time window in your `Settings.yaml`:

```yaml
PunktDe:
  Analytics:
    neos:
      history:
        interval: '2 DAY'   # MySQL INTERVAL expression, e.g. '1 WEEK', '6 MONTH'
    elasticsearch:
      server:
        host: '127.0.0.1'
        port: 9200
        scheme: http
        user: 'elastic'
        pass: ''
```

## Usage

Run a transfer manually:

```bash
./flow transfer:history
./flow transfer:nodedata
```

Both transfers are also registered as [flowpack/task](https://github.com/Flowpack/task) tasks and run daily
(history at 02:05, node data at 02:10). See `Configuration/Settings.Flowpack.yaml`.
