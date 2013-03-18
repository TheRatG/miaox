http://sphinxsearch.com/docs/2.0.2/sphinxql-reference.html

# Examples

Search articles ids and meta info example:

```php
$host = '127.0.0.1';
$port= '4412';

$sphinxql = new Miaox_SphinxQl( $host, $port );

$sphinxql->select()
  ->from( 'articles', 'articles_delta' )
	->match( 'body', 'test' )
	->where( 'is_valid', 1 )
	->where( 'type', Miaox_SphinxQl::QUERY_IN, array( 1, 2, 3 ) )
	->orderBy( 'publish_date' )
	->limit( 2, 2 );

$meta = array();
$result = $sphinxql->execute( null, $meta );
```

Alternative syntax of getting result
```php
$result = $sphinxql->execute();
$meta = $sphinxql->meta();

$sphinxql->enqueue();
$sphinxql->enqueue( Miaox_SphinxQl::SHOW_META );
$resultBatch = $sphinxql->executeBatch();
if ( $resultBatch && isset( $resultBatch[ 0 ], $resultBatch[ 1 ] ) )
{
  $result = $resultBatch[ 0 ];
	$meta = $sphinxql->processingResult( $resultBatch[ 1 ] );
}
```


# Main functions
* execute( string $query = null )

* enqueue( string $query = null )
* executeBatch( array $query )

* callSnippets( array $docs, string $index, string $query, array $opts = array() )
* callSnippets( $text, $index, $hits )
* describe()

# Query Builder

## SELECT

http://sphinxsearch.com/docs/2.0.2/sphinxql-select.html

* select()
* from()
* match()
* where()
* whereOpen()
* whereClose()
* orderBy()
* groupBy()
* withinGroupOrderBy()
* limit()
* offset()
* option()

## INSERT

## REPLACE
