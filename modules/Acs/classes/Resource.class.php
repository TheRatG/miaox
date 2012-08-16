<?php
class Miaox_Acs_Resource
{
	public function getList()
	{
		$result = array();
		$libsDir = Miao_Config::Main()->get( 'paths.libs' );
		$list = $this->_rglob( '*Office/classes', GLOB_ONLYDIR, $libsDir );

		foreach ( $list as $key => $value )
		{
			if ( false !== strpos( $value, 'tests/sources' ) )
			{
				unset( $list[ $key ] );
			}
			if ( false !== strpos( $value, 'miao/' ) )
			{
				unset( $list[ $key ] );
			}
		}

		$classesList = array();
		foreach ( $list as $path )
		{
			$tmp = $this->_rglob( '*', 0, $path );
			foreach ( $tmp as $item )
			{
				$isControl = ( false !== strpos( $item, '/View/' ) ) || ( false !== strpos( $item, '/ViewBlock/' ) ) || ( false !== strpos( $item, '/Action/' ) );
				if ( is_file( $item ) && $isControl )
				{
					$className = $this->_extractClassName( $item );
					$classesList[] = $className;
				}
			}
		}
		$result = array_unique( $classesList );
		return $result;
	}

	protected function _rglob( $pattern = '*', $flags = 0, $path = '' )
	{
		$paths = glob( $path . '*', GLOB_MARK | GLOB_ONLYDIR | GLOB_NOSORT );
		$files = glob( $path . $pattern, $flags );
		foreach ( $paths as $path )
		{
			$files = array_merge( $files, $this->_rglob( $pattern, $flags, $path ) );
		}
		return $files;
	}

	protected function _extractClassName( $filename )
	{
		$fp = fopen( $filename, 'r' );
		$class = $buffer = '';
		$i = 0;
		while ( !$class )
		{
			if ( feof( $fp ) )
				break;

			$buffer .= fread( $fp, 512 );
			$tokens = token_get_all( $buffer );

			if ( strpos( $buffer, '{' ) === false )
				continue;

			for(; $i < count( $tokens ); $i++ )
			{
				if ( $tokens[ $i ][ 0 ] === T_CLASS )
				{
					for( $j = $i + 1; $j < count( $tokens ); $j++ )
					{
						if ( $tokens[ $j ] === '{' )
						{
							$class = $tokens[ $i + 2 ][ 1 ];
						}
					}
				}
			}
		}
		return $class;
	}
}