# Overview
Query builder module for SphinxQl.

* download [Miaox](https://github.com/TheRatG/miaox/archive/master.zip)
* unpack and copy direcotry `modules/SphinxQl`, if you don't use miao framework
* read examples, relax and enjoy

## Why?
1. SphinxQl is faster than SphinxAPI, you can check in [SphinxAPI vs SphinxQL benchmark](http://sphinxsearch.com/blog/2010/04/25/sphinxapi-vs-sphinxql-benchmark)
2. Query is easier for understand, see for [SphinxQL reference](http://sphinxsearch.com/docs/2.0.2/sphinxql-reference.html)

## Why not?
1. You need [php mysqli extension](http://php.net/manual/en/book.mysqli.php)

# Examples

Search articles ids and meta info example

```php
$host = '127.0.0.1';
$port= '4412';

$sphinxql = new Miaox_SphinxQl( $host, $port );

$sphinxql->select( '*' )
    ->from( 'articles', 'articles_delta' )
    ->match( 'body', 'test' )
    ->where( 'is_valid', 1 )
    ->where( 'type', Miaox_SphinxQl::IN, array( 1, 2, 3 ) )
    ->orderBy( 'publish_date' )
    ->limit( 2, 2 );

$meta = array();
$result = $sphinxql->execute( null, $meta );
```

Alternative syntax of getting result, but you make two requests to searchd

```php
$result = $sphinxql->execute( 'SELECT * FROM `articles`' );
$meta = $sphinxql->execute( Miaox_SphinxQl::SHOW_META );
```

Or multiquery version with one request to searchd

```php
$result = array();
$meta = array();
$sphinxql->enqueue( 'SELECT * FROM `articles`' );
$sphinxql->enqueue( Miaox_SphinxQl::SHOW_META );
$resultBatch = $sphinxql->executeBatch();
if ( $resultBatch && isset( $resultBatch[ 0 ], $resultBatch[ 1 ] ) )
{
	$result = $resultBatch[ 0 ];
	$meta = $sphinxql->processingResult( $resultBatch[ 1 ] );
}
```


# Main functions
* execute() - Execute single query

* enqueue() - Add query to queue
* executeBatch() - Execute multi query

* callSnippets() - BuildExcerpts text

# Query Builder

## SELECT

http://sphinxsearch.com/docs/2.0.2/sphinxql-select.html

Begin build your query from start() function, and get query result to execute() function.
If you want get query string, call compile() function.

* select()
* from()
* match()
* where()
* orderBy()
* groupBy()
* limit()
* offset()
* option()
* compile()

### Get total count of documents

```php
//...
$search
    ->select( '1 as dummy', 'count(*) as total' )
    ->from( 'articles', 'articles_delta' )
    ->groupBy( 'dummy' );
$result = $search->execute();
$count = $result[ 0 ][ 'total' ];
```

### Allocation of matches (buildexcerpts) for a group of documents

```php
$opts = array(
    "before_match" => '<span class="mark">',
    "after_match" => "</span>",
    "chunk_separator" => " ... ",
    "limit" => 200,
    "around" => 10
);
$docs = array( 'long body test', 'long body test text 2' );
$result = $search->callSnippets( $docs, 'articles', 'test', $opts );
/**
array(
    'long body <span class="mark">test</span>',
);
*/
```

### Problem with GEODIST function 'unsupported filter type 'intrange' on float column'

Put five zeros after the integer, example:
```
SELECT *, GEODIST( lat, lng, 0.5894, -1.4724 ) AS geodist FROM ... WHERE geodist >= 0.00000
```


## INSERT

## REPLACE
