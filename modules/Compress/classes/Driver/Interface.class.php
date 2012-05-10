<?php
interface Realty_Compress_Driver_Interface
{
	public function minify( $fileList, $dstFilename, array $options = array() );
}