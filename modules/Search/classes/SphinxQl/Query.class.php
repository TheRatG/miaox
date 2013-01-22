<?php
/**
 * @author vpak
 * @date 2013-01-22 09:45:27
 */
class Miaox_Search_SphinxQl_Query
{
	/**
	 * The last compiled query
	 *
	 * @var  string
	 */
	protected $last_compiled = null;

	/**
	 * The last choosen method (select, insert, replace, update, delete)
	 *
	 * @var  string
	 */
	protected $type = null;

	/**
	 * Array of select elements that will be comma separated
	 *
	 * @var  array
	 */
	protected $select = array();

	/**
	 * From in SphinxQL is the list of indexes that will be used
	 *
	 * @var  array
	*/
	protected $from = array();

	/**
	 * The list of where and parenthesis, must be inserted in order
	 *
	 * @var  array
	*/
	protected $where = array();

	/**
	 * The list of matches for the MATCH function in SphinxQL
	 *
	 * @var  array
	*/
	protected $match = array();

	/**
	 * GROUP BY array to be comma separated
	 *
	 * @var  array
	*/
	protected $group_by = array();

	/**
	 * ORDER BY array
	 *
	 * @var  array
	*/
	protected $within_group_order_by = array();

	/**
	 * ORDER BY array
	 *
	 * @var  array
	*/
	protected $order_by = array();

	/**
	 * When not null it adds an offset
	 *
	 * @var  null|int
	*/
	protected $offset = null;

	/**
	 * When not null it adds a limit
	 *
	 * @var  null|int
	 */
	protected $limit = null;

	/**
	 * Value of INTO query for INSERT or REPLACE
	 *
	 * @var  null|string
	 */
	protected $into = null;

	/**
	 * Array of columns for INSERT or REPLACE
	 *
	 * @var  array
	 */
	protected $columns = array();

	/**
	 * Array OF ARRAYS of values for INSERT or REPLACE
	 *
	 * @var  array
	*/
	protected $values = array();

	/**
	 * Array arrays containing column and value for SET in UPDATE
	 *
	 * @var  array
	*/
	protected $set = array();

	/**
	 * Array of OPTION specific to SphinxQL
	 *
	 * @var  array
	*/
	protected $options = array();

	/**
	 * Ready for use queries
	 *
	 * @var  array
	*/
	protected static $show_queries = array(
		'meta' => 'SHOW META',
		'warnings' => 'SHOW WARNINGS',
		'status' => 'SHOW STATUS',
		'tables' => 'SHOW TABLES',
		'variables' => 'SHOW VARIABLES',
		'variablesSession' => 'SHOW SESSION VARIABLES',
		'variablesGlobal' => 'SHOW GLOBAL VARIABLES' );
	protected $_sphinxQl = null;

	public function __construct( $sphinxQl = null )
	{
		$this->_sphinxQl = $sphinxQl;
	}

	public function execute()
	{
		$result = null;
		if ( $this->_sphinxQl )
		{
			$result = $this->_sphinxQl->execute( $this );
		}
		return $result;
	}

	/**
	 * Returns the latest compiled query
	 *
	 * @return  string  The last compiled query
	 */
	public function getCompiled()
	{
		return $this->last_compiled;
	}

	/**
	 * Runs the compile function
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function compile()
	{
		switch ( $this->type )
		{
			case 'select':
				$this->compileSelect();
				break;
			case 'insert':
			case 'replace':
				$this->compileInsert();
				break;
			case 'update':
				$this->compileUpdate();
				break;
			case 'delete':
				$this->compileDelete();
				break;
		}

		return $this;
	}

	/**
	 * Compiles the MATCH part of the queries
	 * Used by: SELECT, DELETE, UPDATE
	 *
	 * @return  string  The compiled MATCH
	 */
	public function compileMatch()
	{
		$query = '';

		if ( !empty( $this->match ) )
		{
			$query .= 'WHERE ';
		}

		if ( !empty( $this->match ) )
		{
			$query .= "MATCH(";

			$pre = '';

			foreach ( $this->match as $match )
			{
				$pre .= '@' . $match[ 'column' ] . ' ';

				if ( $match[ 'half' ] )
				{
					$pre .= $this->halfEscapeMatch( $match[ 'value' ] );
				}
				else
				{
					$pre .= $this->escapeMatch( $match[ 'value' ] );
				}

				$pre .= ' ';
			}

			$query .= $this->escape( trim( $pre ) ) . ") ";
		}

		return $query;
	}

	/**
	 * Compiles the WHERE part of the queries
	 * It interacts with the MATCH() and of course isn't usable stand-alone
	 * Used by: SELECT, DELETE, UPDATE
	 *
	 * @return  string  The compiled WHERE
	 */
	public function compileWhere()
	{
		$query = '';

		if ( empty( $this->match ) && !empty( $this->where ) )
		{
			$query .= 'WHERE ';
		}

		if ( !empty( $this->where ) )
		{
			$just_opened = false;

			foreach ( $this->where as $key => $where )
			{
				if ( in_array( $where[ 'ext_operator' ], array(
					'AND (',
					'OR (',
					')' ) ) )
				{
					// if match is not empty we've got to use an operator
					if ( $key == 0 || !empty( $this->match ) )
					{
						$query .= '(';

						$just_opened = true;
					}
					else
					{
						$query .= $where[ 'ext_operator' ] . ' ';
					}

					continue;
				}

				if ( $key > 0 && !$just_opened || !empty( $this->match ) )
				{
					$query .= $where[ 'ext_operator' ] . ' '; // AND/OR
				}

				$just_opened = false;

				if ( strtoupper( $where[ 'operator' ] ) === 'BETWEEN' )
				{
					$query .= $this->quoteIdentifier( $where[ 'column' ] );
					$query .= ' BETWEEN ';
					$query .= $this->quote( $where[ 'value' ][ 0 ] ) . ' AND ' . $this->quote( $where[ 'value' ][ 1 ] ) . ' ';
				}
				else
				{
					// id can't be quoted!
					if ( $where[ 'column' ] === 'id' )
					{
						$query .= 'id ';
					}
					else
					{
						$query .= $this->quoteIdentifier( $where[ 'column' ] ) . ' ';
					}

					if ( strtoupper( $where[ 'operator' ] ) === 'IN' )
					{
						$query .= 'IN (' . implode( ', ', $this->quoteArr( $where[ 'value' ] ) ) . ') ';
					}
					else
					{
						$query .= $where[ 'operator' ] . ' ' . $this->quote( $where[ 'value' ] ) . ' ';
					}
				}
			}
		}

		return $query;
	}

	/**
	 * Compiles the statements for SELECT
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function compileSelect()
	{
		$query = '';

		if ( $this->type == 'select' )
		{
			$query .= 'SELECT ';

			if ( !empty( $this->select ) )
			{
				$query .= implode( ', ', $this->quoteIdentifierArr( $this->select ) ) . ' ';
			}
			else
			{
				$query .= '* ';
			}
		}

		if ( !empty( $this->from ) )
		{
			$query .= 'FROM ' . implode( ', ', $this->quoteIdentifierArr( $this->from ) ) . ' ';
		}

		$query .= $this->compileMatch() . $this->compileWhere();

		if ( !empty( $this->group_by ) )
		{
			$query .= 'GROUP BY ' . implode( ', ', $this->quoteIdentifierArr( $this->group_by ) ) . ' ';
		}

		if ( !empty( $this->within_group_order_by ) )
		{
			$query .= 'WITHIN GROUP ORDER BY ';

			$order_arr = array();

			foreach ( $this->within_group_order_by as $order )
			{
				$order_sub = $this->quoteIdentifier( $order[ 'column' ] ) . ' ';

				if ( $order[ 'direction' ] !== null )
				{
					$order_sub .= ( ( strtolower( $order[ 'direction' ] ) === 'desc' ) ? 'DESC' : 'ASC' );
				}

				$order_arr[] = $order_sub;
			}

			$query .= implode( ', ', $order_arr ) . ' ';
		}

		if ( !empty( $this->order_by ) )
		{
			$query .= 'ORDER BY ';

			$order_arr = array();

			foreach ( $this->order_by as $order )
			{
				$order_sub = $this->quoteIdentifier( $order[ 'column' ] ) . ' ';

				if ( $order[ 'direction' ] !== null )
				{
					$order_sub .= ( ( strtolower( $order[ 'direction' ] ) === 'desc' ) ? 'DESC' : 'ASC' );
				}

				$order_arr[] = $order_sub;
			}

			$query .= implode( ', ', $order_arr ) . ' ';
		}

		if ( $this->limit !== null || $this->offset !== null )
		{
			if ( $this->offset === null )
			{
				$this->offset = 0;
			}

			if ( $this->limit === null )
			{
				$this->limit = 9999999999999;
			}

			$query .= 'LIMIT ' . ( ( int ) $this->offset ) . ', ' . ( ( int ) $this->limit ) . ' ';
		}

		if ( !empty( $this->options ) )
		{
			$options = array();
			foreach ( $this->options as $option )
			{
				$options[] = $this->quoteIdentifier( $option[ 'name' ] ) . ' = ' . $this->quote( $option[ 'value' ] );
			}

			$query .= 'OPTION ' . implode( ', ', $options );
		}

		$this->last_compiled = $query;

		return $this;
	}

	/**
	 * Compiles the statements for INSERT or REPLACE
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function compileInsert()
	{
		if ( $this->type == 'insert' )
		{
			$query = 'INSERT ';
		}
		else
		{
			$query = 'REPLACE ';
		}

		if ( $this->into !== null )
		{
			$query .= 'INTO ' . $this->into . ' ';
		}

		if ( !empty( $this->columns ) )
		{
			$query .= '(' . implode( ', ', $this->quoteIdentifierArr( $this->columns ) ) . ') ';
		}

		if ( !empty( $this->values ) )
		{
			$query .= 'VALUES ';
			$query_sub = '';
			foreach ( $this->values as $value )
			{
				$query_sub[] = '(' . implode( ', ', $this->quoteArr( $value ) ) . ')';
			}

			$query .= implode( ', ', $query_sub );
		}

		$this->last_compiled = $query;

		return $this;
	}

	/**
	 * Compiles the statements for UPDATE
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function compileUpdate()
	{
		$query = 'UPDATE ';

		if ( $this->into !== null )
		{
			$query .= $this->into . ' ';
		}

		if ( !empty( $this->set ) )
		{

			$query .= 'SET ';

			$query_sub = array();

			foreach ( $this->set as $column => $value )
			{
				// MVA support
				if ( is_array( $value ) )
				{
					$query_sub[] = $this->quoteIdentifier( $column ) . ' = (' . implode( ', ', $this->queryArr( $value ) ) . ')';
				}
				else
				{
					$query_sub[] = $this->quoteIdentifier( $column ) . ' = ' . $this->quote( $value );
				}
			}

			$query .= implode( ', ', $query_sub ) . ' ';
		}

		$query .= $this->compileMatch() . $this->compileWhere();

		$this->last_compiled = $query;

		return $this;
	}

	/**
	 * Compiles the statements for DELETE
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function compileDelete()
	{
		$query = 'DELETE ';

		if ( !empty( $this->from ) )
		{
			$query .= 'FROM ' . $this->from[ 0 ] . ' ';
		}

		if ( !empty( $this->where ) )
		{
			$query .= $this->compileWhere();
		}

		$this->last_compiled = $query;

		return $this;
	}

	/**
	 * Select the columns
	 * Gets the arguments passed as $sphinxql->select('one', 'two')
	 * Using it without arguments equals to having '*' as argument
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function select()
	{
		$this->type = 'select';
		$this->select = func_get_args();
		return $this;
	}

	/**
	 * Activates the INSERT mode
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function doInsert()
	{
		if ( $this->type !== null )
		{
			$new = static::forge( $this->conn );
			$new->insert();
			return $new;
		}

		$this->type = 'insert';

		return $this;
	}

	/**
	 * Activates the REPLACE mode
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function doReplace()
	{
		if ( $this->type !== null )
		{
			$new = static::forge( $this->conn );
			$new->replace();
			return $new;
		}

		$this->type = 'replace';

		return $this;
	}

	/**
	 * Activates the UPDATE mode
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function doUpdate( $index )
	{
		if ( $this->type !== null )
		{
			$new = static::forge( $this->conn );
			$new->update( $index );
			$new->into( $index );
			return $new;
		}

		$this->type = 'update';
		$this->into( $index );

		return $this;
	}

	/**
	 * Activates the DELETE mode
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function doDelete()
	{
		if ( $this->type !== null )
		{
			$new = static::forgeWithConnection( $this->conn );
			$new->delete();
			return $new;
		}

		$this->type = 'delete';

		return $this;
	}

	/**
	 * FROM clause (Sphinx-specific since it works with multiple indexes)
	 * func_get_args()-enabled
	 *
	 * @param  array  $array  An array of indexes to use
	 *
	 * @return Miaox_Search_SphinxQl_Query  The current object
	 */
	public function from( $array = null )
	{
		if ( is_string( $array ) )
		{
			$this->from = func_get_args();
		}

		if ( is_array( $array ) )
		{
			$this->from = $array;
		}

		return $this;
	}

	/**
	 * MATCH clause (Sphinx-specific)
	 *
	 * @param  string   $column  The column name
	 * @param  string   $value   The value
	 * @param  boolean  $half    Exclude ", |, - control characters from being escaped
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function match( $column, $value, $half = false )
	{
		$this->match[] = array(
			'column' => $column,
			'value' => $value,
			'half' => $half );

		return $this;
	}

	/**
	 * WHERE clause
	 *
	 * Examples:
	 *		$sq->where('column', 'value');
	 *		// WHERE `column` = 'value'
	 *
	 *		$sq->where('column', '=', 'value');
	 *		// WHERE `column` = 'value'
	 *
	 *		$sq->where('column', '>=', 'value')
	 *		// WHERE `column` >= 'value'
	 *
	 *		$sq->where('column', 'IN', array('value1', 'value2', 'value3'));
	 *		// WHERE `column` IN ('value1', 'value2', 'value3')
	 *
	 *		$sq->where('column', 'BETWEEN', array('value1', 'value2'))
	 *		// WHERE `column` BETWEEN 'value1' AND 'value2'
	 *		// WHERE `example` BETWEEN 10 AND 100
	 *
	 * @param  string   $column    The column name
	 * @param  string   $operator  The operator to use
	 * @param  string   $value     The value to check against
	 * @param  boolean  $or        If it should be prepended with OR (true) or AND (false) - not available as for Sphinx 2.0.2
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function where( $column, $operator, $value = null, $or = false )
	{
		if ( $value === null )
		{
			$value = $operator;
			$operator = '=';
		}

		$this->where[] = array(
			'ext_operator' => $or ? 'OR' : 'AND',
			'column' => $column,
			'operator' => $operator,
			'value' => $value );

		return $this;
	}

	/**
	 * OR WHERE - at this time (Sphinx 2.0.2) it's not available
	 *
	 * @param  string  $column    The column name
	 * @param  string  $operator  The operator to use
	 * @param  mixed   $value     The value to compare against
	 *
	 * @return Miaox_Search_SphinxQl_Query  The current object
	 */
	public function orWhere( $column, $operator, $value = null )
	{
		$this->where( $column, $operator, $value, true );

		return $this;
	}

	/**
	 * Opens a parenthesis prepended with AND (where necessary)
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function whereOpen()
	{
		$this->where[] = array(
			'ext_operator' => 'AND (' );

		return $this;
	}

	/**
	 * Opens a parenthesis prepended with OR (where necessary)
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function orWhereOpen()
	{
		$this->where[] = array(
			'ext_operator' => 'OR (' );

		return $this;
	}

	/**
	 * Closes a parenthesis in WHERE
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function whereClose()
	{
		$this->where[] = array(
			'ext_operator' => ')' );

		return $this;
	}

	/**
	 * GROUP BY clause
	 * Adds to the previously added columns
	 *
	 * @param  string  $column  A column to group by
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function groupBy( $column )
	{
		$this->group_by[] = $column;

		return $this;
	}

	/**
	 * WITHIN GROUP ORDER BY clause (SphinxQL-specific)
	 * Adds to the previously added columns
	 * Works just like a classic ORDER BY
	 *
	 * @param  string  $column     The column to group by
	 * @param  string  $direction  The group by direction (asc/desc)
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function withinGroupOrderBy( $column, $direction = null )
	{
		$this->within_group_order_by[] = array(
			'column' => $column,
			'direction' => $direction );

		return $this;
	}

	/**
	 * ORDER BY clause
	 * Adds to the previously added columns
	 *
	 * @param  string  $column     The column to order on
	 * @param  string  $direction  The ordering direction (asc/desc)
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function orderBy( $column, $direction = null )
	{
		$this->order_by[] = array(
			'column' => $column,
			'direction' => $direction );

		return $this;
	}

	/**
	 * LIMIT clause
	 * Supports also LIMIT offset, limit
	 *
	 * @param  int       $offset  Offset if $limit is specified, else limit
	 * @param  null|int  $limit   The limit to set, null for no limit
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function limit( $offset, $limit = null )
	{
		if ( $limit === null )
		{
			$this->limit = ( int ) $offset;
			return $this;
		}

		$this->offset( $offset );
		$this->limit = ( int ) $limit;

		return $this;
	}

	/**
	 * OFFSET clause
	 *
	 * @param  int  $offset  The offset
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function offset( $offset )
	{
		$this->offset = ( int ) $offset;

		return $this;
	}

	/**
	 * OPTION clause (SphinxQL-specific)
	 * Used by: SELECT
	 *
	 * @param  string  $name   Option name
	 * @param  string  $value  Option value
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function option( $name, $value )
	{
		$this->options[] = array(
			'name' => $name,
			'value' => $value );

		return $this;
	}

	/**
	 * INTO clause
	 * Used by: INSERT, REPLACE
	 *
	 * @param  string  $index  The index to insert/replace into
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function into( $index )
	{
		$this->into = $index;

		return $this;
	}

	/**
	 * Set columns
	 * Used in: INSERT, REPLACE
	 * func_get_args()-enabled
	 *
	 * @param  array  $array  The array of columns
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function columns( $array = array() )
	{
		if ( is_array( $array ) )
		{
			$this->columns = $array;
		}
		else
		{
			$this->columns =\func_get_args();
		}

		return $this;
	}

	/**
	 * Set VALUES
	 * Used in: INSERT, REPLACE
	 * func_get_args()-enabled
	 *
	 * @param  array  $array  The array of values matching the columns from $this->columns()
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function values( $array )
	{
		if ( is_array( $array ) )
		{
			$this->values[] = $array;
		}
		else
		{
			$this->values[] =\func_get_args();
		}

		return $this;
	}

	/**
	 * Set column and relative value
	 * Used in: INSERT, REPLACE
	 *
	 * @param  string  $column  The column name
	 * @param  string  $value   The value
	 *
	 * @return  Miaox_Search_SphinxQl_Query  The current object
	 */
	public function value( $column, $value )
	{
		if ( $this->type === 'insert' || $this->type === 'replace' )
		{
			$this->columns[] = $column;
			$this->values[ 0 ][] = $value;
		}
		else
		{
			$this->set[ $column ] = $value;
		}

		return $this;
	}

	/**
	 * Allows passing an array with the key as column and value as value
	 * Used in: INSERT, REPLACE, UPDATE
	 *
	 * @param  array  $array  Array of key-values
	 *
	 * @return Miaox_Search_SphinxQl_Query  The current object
	 */
	public function set( $array )
	{
		foreach ( $array as $key => $item )
		{
			$this->value( $key, $item );
		}

		return $this;
	}

	/**
	 * Escapes the query for the MATCH() function
	 *
	 * @param  string  $string The string to escape for the MATCH
	 *
	 * @return  string  The escaped string
	 */
	public function escapeMatch( $string )
	{
		$from = array(
			'\\',
			'(',
			')',
			'|',
			'-',
			'!',
			'@',
			'~',
			'"',
			'&',
			'/',
			'^',
			'$',
			'=' );
		$to = array(
			'\\\\',
			'\(',
			'\)',
			'\|',
			'\-',
			'\!',
			'\@',
			'\~',
			'\"',
			'\&',
			'\/',
			'\^',
			'\$',
			'\=' );

		return str_replace( $from, $to, $string );
	}

	/**
	 * Escapes the query for the MATCH() function
	 * Allows some of the control characters to pass through for use with a search field: -, |, "
	 * It also does some tricks to wrap/unwrap within " the string and prevents errors
	 *
	 * @param  string  $string  The string to escape for the MATCH
	 *
	 * @return  string  The escaped string
	 */
	public function halfEscapeMatch( $string )
	{
		$from_to = array(
			'\\' => '\\\\',
			'(' => '\(',
			')' => '\)',
			'!' => '\!',
			'@' => '\@',
			'~' => '\~',
			'&' => '\&',
			'/' => '\/',
			'^' => '\^',
			'$' => '\$',
			'=' => '\=' );

		$string = str_replace( array_keys( $from_to ), array_values( $from_to ), $string );

		// this manages to lower the error rate by a lot
		if ( substr_count( $string, '"' ) % 2 !== 0 )
		{
			$string .= '"';
		}

		$from_to_preg = array(
			"'\"([^\s]+)-([^\s]*)\"'" => "\\1\-\\2",
			"'([^\s]+)-([^\s]*)'" => "\"\\1\-\\2\"" );

		$string = preg_replace( array_keys( $from_to_preg ), array_values( $from_to_preg ), $string );

		return $string;
	}

	/**
	 * Runs $this->quoteIdentifier on every element of an array
	 *
	 * @param  array  $array  An array of strings to be quoted
	 *
	 * @return  array  The array of quoted strings
	 */
	public function quoteIdentifierArr( Array $array = array() )
	{
		$result = array();

		foreach ( $array as $key => $item )
		{
			$result[ $key ] = $this->quoteIdentifier( $item );
		}

		return $result;
	}

	public function quoteIdentifier( $value )
	{
		if ( $value instanceof Miaox_Search_SphinxQl_Query_Expression )
		{
			return $value->value();
		}

		if ( $value === '*' )
		{
			return $value;
		}

		$pieces = explode( '.', $value );

		foreach ( $pieces as $key => $piece )
		{
			$pieces[ $key ] = '`' . $piece . '`';
		}

		return implode( '.', $pieces );
	}
}