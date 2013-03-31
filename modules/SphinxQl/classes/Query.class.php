<?php
/**
 * @author vpak
 * @date 2013-01-22 09:45:27
 */
class Miaox_SphinxQl_Query
{
	/**
	 * The last compiled query
	 *
	 * @var  string
	 */
	protected $_lastCompiled = null;

	/**
	 * The last choosen method (select, insert, replace, update, delete)
	 *
	 * @var  string
	 */
	protected $_type = null;

	/**
	 * Array of select elements that will be comma separated
	 *
	 * @var  array
	 */
	protected $_select = array();

	/**
	 * From in SphinxQL is the list of indexes that will be used
	 *
	 * @var  array
	*/
	protected $_from = array();

	/**
	 * The list of where and parenthesis, must be inserted in order
	 *
	 * @var  array
	*/
	protected $_where = array();

	/**
	 * The list of matches for the MATCH function in SphinxQL
	 *
	 * @var  array
	*/
	protected $_match = array();

	/**
	 * GROUP BY array to be comma separated
	 *
	 * @var  array
	*/
	protected $_groupBy = array();

	/**
	 * ORDER BY array
	 *
	 * @var  array
	*/
	protected $_withinGroupOrderBy = array();

	/**
	 * ORDER BY array
	 *
	 * @var  array
	*/
	protected $_orderBy = array();

	/**
	 * When not null it adds an offset
	 *
	 * @var  null|int
	*/
	protected $_offset = null;

	/**
	 * When not null it adds a limit
	 *
	 * @var  null|int
	 */
	protected $_limit = null;

	/**
	 * Value of INTO query for INSERT or REPLACE
	 *
	 * @var  null|string
	 */
	protected $_into = null;

	/**
	 * Array of columns for INSERT or REPLACE
	 *
	 * @var  array
	 */
	protected $_columns = array();

	/**
	 * Array OF ARRAYS of values for INSERT or REPLACE
	 *
	 * @var  array
	*/
	protected $_values = array();

	/**
	 * Array arrays containing column and value for SET in UPDATE
	 *
	 * @var  array
	*/
	protected $_set = array();

	/**
	 * Array of OPTION specific to SphinxQL
	 *
	 * @var  array
	*/
	protected $_options = array();

	/**
	 * Select the columns
	 * Gets the arguments passed as $sphinxql->select('one', 'two')
	 * Using it without arguments equals to having '*' as argument
	 *
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function select()
	{
		$this->_clearProperties();

		$this->_type = 'select';
		$this->_select = func_get_args();
		return $this;
	}

	/**
	 * FROM clause (Sphinx-specific since it works with multiple indexes)
	 * func_get_args()-enabled
	 *
	 * @param  array  $array  An array of indexes to use
	 *
	 * @return Miaox_SphinxQl_Query  The current object
	 */
	public function from( $array = null )
	{
		if ( is_string( $array ) )
		{
			$this->_from = func_get_args();
		}

		if ( is_array( $array ) )
		{
			$this->_from = $array;
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
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function match( $column, $value, $half = false )
	{
		$this->_match[] = array(
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
	 *
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function where( $column, $operator, $value = null )
	{
		if ( $value === null )
		{
			$value = $operator;
			$operator = '=';
		}

		$this->_where[] = array(
			'ext_operator' => 'AND',
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
	 * @return Miaox_SphinxQl_Query  The current object
	 */
	public function orWhere( $column, $operator, $value = null )
	{
		$this->where( $column, $operator, $value, true );

		return $this;
	}

	/**
	 * Opens a parenthesis prepended with AND (where necessary)
	 *
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function whereOpen()
	{
		$this->_where[] = array(
			'ext_operator' => 'AND (' );

		return $this;
	}

	/**
	 * Opens a parenthesis prepended with OR (where necessary)
	 *
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function orWhereOpen()
	{
		$this->_where[] = array(
			'ext_operator' => 'OR (' );

		return $this;
	}

	/**
	 * Closes a parenthesis in WHERE
	 *
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function whereClose()
	{
		$this->_where[] = array(
			'ext_operator' => ')' );

		return $this;
	}

	/**
	 * GROUP BY clause
	 * Adds to the previously added columns
	 *
	 * @param  string  $column  A column to group by
	 *
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function groupBy( $column )
	{
		$this->_groupBy[] = $column;

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
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function withinGroupOrderBy( $column, $direction = null )
	{
		$this->_withinGroupOrderBy[] = array(
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
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function orderBy( $column, $direction = null )
	{
		$this->_orderBy[] = array(
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
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function limit( $offset, $limit = null )
	{
		if ( $limit === null )
		{
			$this->_limit = ( int ) $offset;
			return $this;
		}

		$this->offset( $offset );
		$this->_limit = ( int ) $limit;

		return $this;
	}

	/**
	 * OFFSET clause
	 *
	 * @param  int  $offset  The offset
	 *
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function offset( $offset )
	{
		$this->_offset = ( int ) $offset;

		return $this;
	}

	/**
	 * OPTION clause (SphinxQL-specific)
	 * Used by: SELECT
	 *
	 * @param  string  $name   Option name
	 * @param  string  $value  Option value
	 *
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function option( $name, $value )
	{
		$this->_options[] = array(
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
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function into( $index )
	{
		$this->_into = $index;

		return $this;
	}

	/**
	 * Set columns
	 * Used in: INSERT, REPLACE
	 * func_get_args()-enabled
	 *
	 * @param  array  $array  The array of columns
	 *
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function columns( $array = array() )
	{
		if ( is_array( $array ) )
		{
			$this->_columns = $array;
		}
		else
		{
			$this->_columns = \func_get_args();
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
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function values( $array )
	{
		if ( is_array( $array ) )
		{
			$this->_values[] = $array;
		}
		else
		{
			$this->_values[] = \func_get_args();
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
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function value( $column, $value )
	{
		if ( $this->_type === 'insert' || $this->_type === 'replace' )
		{
			$this->_columns[] = $column;
			$this->_values[ 0 ][] = $value;
		}
		else
		{
			$this->_set[ $column ] = $value;
		}

		return $this;
	}

	/**
	 * Allows passing an array with the key as column and value as value
	 * Used in: INSERT, REPLACE, UPDATE
	 *
	 * @param  array  $array  Array of key-values
	 *
	 * @return Miaox_SphinxQl_Query  The current object
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
	 * Returns the latest compiled query
	 *
	 * @return  string  The last compiled query
	 */
	public function getCompiled()
	{
		return $this->_lastCompiled;
	}

	/**
	 * Runs the compile function
	 *
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	public function compile()
	{
		switch ( $this->_type )
		{
			case 'select':
				$this->_compileSelect();
				break;
			case 'insert':
			case 'replace':
				$this->_compileInsert();
				break;
			case 'update':
				$this->_compileUpdate();
				break;
			case 'delete':
				$this->_compileDelete();
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
	protected function _compileMatch()
	{
		$pieces = array();

		if ( !empty( $this->_match ) )
		{
			$pieces[] = 'WHERE';
		}

		if ( !empty( $this->_match ) )
		{
			$pieces[] = "MATCH(";

			$pre = '';
			foreach ( $this->_match as $key => $match )
			{
				if ( '' === $match['column'] )
				{
					$pre .= $this->_compileMatchItem($match);
				}
			}

			foreach ( $this->_match as $match )
			{
				if ( '' !== $match['column'] )
				{
					$pre .= $this->_compileMatchItem($match);
				}
			}

			$pieces[] = $this->_escape( trim( $pre ) ) . " )";
		}

		$result = '';
		if ( !empty( $pieces ) )
		{
			$result = implode( ' ', $pieces );
		}
		return $result;
	}

	protected function _compileMatchItem( $match )
	{
		$result = '';
		if ( !empty( $match[ 'column' ] ) )
		{
			$result .= '@' . $match[ 'column' ] . ' ';
		}

		if ( $match[ 'half' ] )
		{
			$result .= $match[ 'value' ];
		}
		else
		{
			$result .= $this->_escapeMatch( $match[ 'value' ] );
		}

		$result .= ' ';
		return $result;
	}

	/**
	 * Compiles the WHERE part of the queries
	 * It interacts with the MATCH() and of course isn't usable stand-alone
	 * Used by: SELECT, DELETE, UPDATE
	 *
	 * @return  string  The compiled WHERE
	 */
	protected function _compileWhere()
	{
		$pieces = array();

		if ( empty( $this->_match ) && !empty( $this->_where ) )
		{
			$pieces[] = 'WHERE';
		}

		if ( !empty( $this->_where ) )
		{
			$just_opened = false;

			foreach ( $this->_where as $key => $where )
			{
				if ( in_array( $where[ 'ext_operator' ], array(
					'AND (',
					'OR (',
					')' ) ) )
				{
					// if match is not empty we've got to use an operator
					if ( $key == 0 || !empty( $this->_match ) )
					{
						$pieces[] = '(';

						$just_opened = true;
					}
					else
					{
						$pieces[] = $where[ 'ext_operator' ];
					}

					continue;
				}

				if ( $key > 0 && !$just_opened || !empty( $this->_match ) )
				{
					$pieces[] = $where[ 'ext_operator' ]; // AND/OR
				}

				$just_opened = false;

				if ( strtoupper( $where[ 'operator' ] ) === 'BETWEEN' )
				{
					$pieces[] = $this->_quoteIdentifier( $where[ 'column' ] );
					$pieces[] = 'BETWEEN';
					$pieces[] = $this->_quote( $where[ 'value' ][ 0 ] ) . ' AND ' . $this->_quote( $where[ 'value' ][ 1 ] );
				}
				else
				{
					// id can't be quoted!
					if ( $where[ 'column' ] === 'id' )
					{
						$pieces[] = 'id';
					}
					else
					{
						$pieces[] = $this->_quoteIdentifier( $where[ 'column' ] );
					}

					if ( strtoupper( $where[ 'operator' ] ) === 'IN' )
					{
						if ( !is_array( $where[ 'value' ] ) )
						{
							$where[ 'value' ] = array(
								$where[ 'value' ] );
						}
						$pieces[] = 'IN (' . implode( ', ', $this->_quoteArr( $where[ 'value' ] ) ) . ')';
					}
					else if ( strtoupper( $where[ 'operator' ] ) === 'NOT IN' )
					{
						if ( !is_array( $where[ 'value' ] ) )
						{
							$where[ 'value' ] = array(
								$where[ 'value' ] );
						}
						$pieces[] = 'NOT IN (' . implode( ', ', $this->_quoteArr( $where[ 'value' ] ) ) . ')';
					}
					else
					{
						$pieces[] = $where[ 'operator' ] . ' ' . $this->_quote( $where[ 'value' ] );
					}
				}
			}
		}

		$result = '';
		if ( !empty( $pieces ) )
		{
			$result = implode( ' ', $pieces );
		}
		return $result;
	}

	/**
	 * Compiles the statements for SELECT
	 *
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	protected function _compileSelect()
	{
		$query = array();

		if ( $this->_type == 'select' )
		{
			$query[] = 'SELECT';

			if ( !empty( $this->_select ) )
			{
				$selTmp = array();
				foreach ( $this->_select as $selItem )
				{
					if ( false !== strpos( $selItem, '(' ) || false !== stripos( $selItem, 'as' ) )
					{
						$selTmp[] = $selItem;
					}
					else
					{
						$selTmp[] = $this->_quoteIdentifier( $selItem );
					}
				}
				$query[] = implode( ', ', $selTmp );
			}
			else
			{
				$query[] = '*';
			}
		}

		if ( !empty( $this->_from ) )
		{
			$query[] = 'FROM ' . implode( ', ', $this->_quoteIdentifierArr( $this->_from ) );
		}

		$match = $this->_compileMatch();
		if ( !empty( $match ) )
		{
			$query[] = $match;
		}

		$where = $this->_compileWhere();
		if ( !empty( $where ) )
		{
			$query[] = $where;
		}

		if ( !empty( $this->_groupBy ) )
		{
			$query[] = 'GROUP BY ' . implode( ', ', $this->_quoteIdentifierArr( $this->_groupBy ) );
		}

		if ( !empty( $this->_withinGroupOrderBy ) )
		{
			$query[] = 'WITHIN GROUP ORDER BY ';

			$order_arr = array();

			foreach ( $this->_withinGroupOrderBy as $order )
			{
				$order_sub = $this->_quoteIdentifier( $order[ 'column' ] );

				if ( $order[ 'direction' ] !== null )
				{
					$order_sub[] = ( ( strtolower( $order[ 'direction' ] ) === 'desc' ) ? 'DESC' : 'ASC' );
				}

				$order_arr[] = $order_sub;
			}

			$query[] = implode( ', ', $order_arr );
		}

		if ( !empty( $this->_orderBy ) )
		{
			$query[] = 'ORDER BY';

			$order_arr = array();

			foreach ( $this->_orderBy as $order )
			{
				$order_sub = $this->_quoteIdentifier( $order[ 'column' ] );
				if ( $order[ 'direction' ] !== null )
				{
					$order_sub .= ' ' . ( ( strtolower( $order[ 'direction' ] ) === 'desc' ) ? 'DESC' : 'ASC' );
				}

				$order_arr[] = $order_sub;
			}

			$query[] = implode( ', ', $order_arr );
		}

		if ( $this->_limit !== null || $this->_offset !== null )
		{
			if ( $this->_offset === null )
			{
				$this->_offset = 0;
			}

			if ( $this->_limit === null )
			{
				$this->_limit = 9999999999999;
			}

			$query[] = 'LIMIT ' . ( ( int ) $this->_offset ) . ', ' . ( ( int ) $this->_limit );
		}

		if ( !empty( $this->_options ) )
		{
			$options = array();
			foreach ( $this->_options as $option )
			{
				$options[] = $this->_quoteIdentifier( $option[ 'name' ] ) . ' = ' . $this->_quote( $option[ 'value' ] );
			}

			$query[] = 'OPTION ' . implode( ', ', $options );
		}

		$this->_lastCompiled = implode( ' ', $query );
		return $this;
	}

	/**
	 * Compiles the statements for INSERT or REPLACE
	 *
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	protected function _compileInsert()
	{
		if ( $this->_type == 'insert' )
		{
			$query = 'INSERT ';
		}
		else
		{
			$query = 'REPLACE ';
		}

		if ( $this->_into !== null )
		{
			$query .= 'INTO ' . $this->_into . ' ';
		}

		if ( !empty( $this->_columns ) )
		{
			$query .= '(' . implode( ', ', $this->_quoteIdentifierArr( $this->_columns ) ) . ') ';
		}

		if ( !empty( $this->_values ) )
		{
			$query .= 'VALUES ';
			$query_sub = '';
			foreach ( $this->_values as $value )
			{
				$query_sub[] = '(' . implode( ', ', $this->_quoteArr( $value ) ) . ')';
			}

			$query .= implode( ', ', $query_sub );
		}

		$this->_lastCompiled = $query;

		return $this;
	}

	/**
	 * Compiles the statements for UPDATE
	 *
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	protected function _compileUpdate()
	{
		$query = 'UPDATE ';

		if ( $this->_into !== null )
		{
			$query .= $this->_into . ' ';
		}

		if ( !empty( $this->_set ) )
		{

			$query .= 'SET ';

			$query_sub = array();

			foreach ( $this->_set as $column => $value )
			{
				// MVA support
				if ( is_array( $value ) )
				{
					$query_sub[] = $this->_quoteIdentifier( $column ) . ' = (' . implode( ', ', $this->queryArr( $value ) ) . ')';
				}
				else
				{
					$query_sub[] = $this->_quoteIdentifier( $column ) . ' = ' . $this->_quote( $value );
				}
			}

			$query .= implode( ', ', $query_sub ) . ' ';
		}

		$query .= $this->_compileMatch() . $this->_compileWhere();

		$this->_lastCompiled = $query;

		return $this;
	}

	/**
	 * Compiles the statements for DELETE
	 *
	 * @return  Miaox_SphinxQl_Query  The current object
	 */
	protected function _compileDelete()
	{
		$query = 'DELETE ';

		if ( !empty( $this->_from ) )
		{
			$query .= 'FROM ' . $this->_from[ 0 ] . ' ';
		}

		if ( !empty( $this->_where ) )
		{
			$query .= $this->_compileWhere();
		}

		$this->_lastCompiled = $query;

		return $this;
	}

	/**
	 * Escapes the query for the MATCH() function
	 *
	 * @param  string  $string The string to escape for the MATCH
	 *
	 * @return  string  The escaped string
	 */
	protected function _escapeMatch( $string )
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
	protected function _halfEscapeMatch( $string )
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
	protected function _quoteIdentifierArr( Array $array = array() )
	{
		$result = array();

		foreach ( $array as $key => $item )
		{
			$result[ $key ] = $this->_quoteIdentifier( $item );
		}

		return $result;
	}

	protected function _quoteIdentifier( $value )
	{
		if ( $value instanceof Miaox_SphinxQl_Query_Expression )
		{
			return $value->value();
		}

		if ( $value === '*' || $value[ 0 ] == '@' )
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

	/**
	 * Adds quotes where necessary for values
	 * Taken from FuelPHP and edited
	 *
	 * @param  Miaox_SphinxQl_Query_Expression  $value  The input string, eventually wrapped in an expression to leave it untouched
	 *
	 * @return  Miaox_SphinxQl_Query_Expression The untouched Expression or the quoted string
	 */
	protected function _quote( $value )
	{
		if ( $value === null )
		{
			return 'null';
		}
		elseif ( $value === true )
		{
			return "'1'";
		}
		elseif ( $value === false )
		{
			return "'0'";
		}
		elseif ( $value instanceof Miaox_SphinxQl_Query_Expression )
		{
			// Use a raw expression
			return $value->value();
		}
		elseif ( is_int( $value ) )
		{
			return ( int ) $value;
		}
		elseif ( is_float( $value ) )
		{
			// Convert to non-locale aware float to prevent possible commas
			return sprintf( '%F', $value );
		}

		return $this->_escape( $value );
	}

	/**
	 * Runs $this->quote() on every element of an array
	 *
	 * @param  array  $array  The array of strings to quote
	 *
	 * @return  array  The array of quotes strings
	 */
	protected function _quoteArr( Array $array = array() )
	{
		$result = array();

		foreach ( $array as $key => $item )
		{
			$result[ $key ] = $this->_quote( $item );
		}

		return $result;
	}

	protected function _compileCallSnippets( $docs, $index, $query, $opts )
	{
		if ( !is_array( $docs ) )
		{
			$docs = array(
				$docs );
		}
		$buildQuery = '';
		$args = array();
		$parts = array();
		foreach ( $docs as $key => $item )
		{
			$parts[] = $this->_quote( $item );
		}
		$args[] = '( ' . implode( ', ', $parts ) . ' )';
		$args[] = "'" . $index . "'";
		$args[] = "'" . $query . "'";
		foreach ( $opts as $optKey => $optValue )
		{
			$optValue = $this->_quote( $optValue );
			$args[] = $optValue . " AS " . $optKey;
		}
		$args = implode( ', ', $args );
		$result = sprintf( 'CALL SNIPPETS( %s )', $args );
		return $result;
	}

	protected function _clearProperties()
	{
		$this->_select = array();
		$this->_from = array();
		$this->_where = array();
		$this->_match = array();
		$this->_groupBy = array();
		$this->_withinGroupOrderBy = array();
		$this->_orderBy = array();
		$this->_offset = null;
		$this->_limit = null;
		$this->_into = null;
		$this->_columns = array();
		$this->_values = array();
		$this->_set = array();

		//$this->_options = array();
	}
}