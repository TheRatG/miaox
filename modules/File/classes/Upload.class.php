<?php
/**
 * @author vpak
 * @date 2012-08-28 15:31:57
 */
class Miaox_File_Upload
{

	protected $_baseDir;

	protected $_mode;

	/**
	 *
	 * @param string $baseDir Base dir for saving files
	 */
	public function __construct( $baseDir, $mode = 0777 )
	{
		$this->_baseDir = $baseDir;
		$this->_mode = $mode;
	}

	/**
	 *
	 * @param array $files $_FILES
	 * @return array
	 */
	public function runByFiles( $files )
	{
		$result = array();
		foreach ( $files as $index => $file )
		{
			$filename = $file[ 'tmp_name' ];

			$name = $this->_getSafeName( $file[ 'name' ] );
			$destination = $this->_baseDir . '/' . $name;
			Miaox_File::mkdir( dirname( $destination ), 0777 );

			move_uploaded_file( $filename, $destination );

			$result[ $index ] = $this->run( $destination );
			unlink( $destination );
		}
		return $result;
	}

	public function run( $file )
	{
		$newFilename = $this->getFilename( $file );
		$dirname = dirname( $newFilename );
		if ( !file_exists( $dirname ) )
		{
			Miaox_File::mkdir( $dirname, $this->_mode, true );
		}
		$res = copy( $file, $newFilename );
		return $newFilename;
	}

	public function getFilename( $file )
	{
		$hash = Miaox_File::hash( $file );
		$ext = Miaox_File::getExtension( $file );
		$addDir = $this->getAddDirByHash( $hash );

		$result = array();
		$result[] = $this->_baseDir . $addDir;
		$result[] = $hash . '.' . $ext;
		$result = implode( DIRECTORY_SEPARATOR, $result );
		return $result;
	}

	public function getAddDirByHash( $hash, $cntSubDir = 2, $cntChar = 2 )
	{
		$hash = trim( $hash );
		if ( !is_numeric( $hash ) )
		{
			$hash = Miaox_File::convert( $hash );
		}

		//add zero
		$len = strlen( $hash );
		$len = $len - $cntSubDir * $cntChar;
		if ( $len < 0 )
		{
			$hash = str_repeat( '0', $len * ( -1 ) ) . $hash;
		}

		$ar = str_split( $hash, $cntChar );
		$ar = array_slice( $ar, count( $ar ) - $cntSubDir, $cntSubDir );
		$result = DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $ar );
		return $result;
	}

	/**
	 * Получить допустимое имя, близкое к оригиналу, но не содержашее спец символов
	 *
	 * @param string $real_name
	 * @return string
	 */
	private function _getSafeName( $real_name )
	{
		$rus = array(
			'ё',
			'ж',
			'ц',
			'ч',
			'ш',
			'щ',
			'ю',
			'я',
			'Ё',
			'Ж',
			'Ц',
			'Ч',
			'Ш',
			'Щ',
			'Ю',
			'Я' );
		$lat = array(
			'yo',
			'zh',
			'tc',
			'ch',
			'sh',
			'sh',
			'yu',
			'ya',
			'YO',
			'ZH',
			'TC',
			'CH',
			'SH',
			'SH',
			'YU',
			'YA' );
		$real_name = str_replace( $rus, $lat, $real_name );
		$real_name = strtr( $real_name, "АБВГДЕЗИЙКЛМНОПРСТУФХЪЫЬЭабвгдезийклмнопрстуфхъыьэ", "ABVGDEZIJKLMNOPRSTUFH_I_Eabvgdezijklmnoprstufh_i_e" );
		return preg_replace( '#[^-a-zA-Z0-9._]#', '_', $real_name );
	}
}