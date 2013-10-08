<?php
require_once 'Exception.class.php';
require_once 'Catcher.class.php';

/**
 * Перехватывает ошибки выполнения и генерирует одноименные исключения.
 *
 * Создаем объект Miaox_Exceptionizer, пользуемся исключениями.
 * Когда исключения не нужны уничтожаем объект.
 *
 * @package Core
 * @example
 * <code>
 *
 * $exceptionizer = new Miaox_Exceptionizer( E_ALL );
 * try
 * {
 * 		$x = 8/0;
 * }
 * catch ( E_WARNING $e )
 * {
 * 		echo get_class( $e );
 * }
 * // E_WARNING
 *
 * try
 * {
 * 		$x = 8 + $y;
 * }
 * catch ( E_WARNING $e )
 * {
 * 		echo get_class( $e );
 * }
 * // E_NOTICE
 * unset( $exceptionizer );
 *
 * $x = 8/0; //Warning: Division by zero in ...
 *
 * </code>
 */
class Miaox_Exceptionizer
{

	public function __construct( $mask = E_ALL, $ignoreOther = false )
	{
		$catcher = new Miaox_Exceptionizer_Catcher();
		$catcher->mask = $mask;
		$catcher->ignoreOther = $ignoreOther;
		$catcher->prevHdl = set_error_handler( array( $catcher, "handler" ) );
	}

	public function __destruct()
	{
		restore_error_handler();
	}
}

/**
 * The logic is: if we catch E_WARNING, we also need NOT to pass out
 * E_NOTICE, but we must let E_ERROR to be passed thru.
 */
class E_CORE_ERROR extends Miaox_Exceptionizer_Exception
{
}
class E_CORE_WARNING extends E_CORE_ERROR
{
}
class E_COMPILE_ERROR extends E_CORE_ERROR
{
}
class E_COMPILE_WARNING extends E_COMPILE_ERROR
{
}
class E_ERROR extends E_CORE_ERROR
{
}
class E_RECOVERABLE_ERROR extends E_ERROR
{
}
class E_PARSE extends E_RECOVERABLE_ERROR
{
}
class E_WARNING extends E_PARSE
{
}
class E_NOTICE extends E_WARNING
{
}
class E_STRICT extends E_NOTICE
{
}
class E_DEPRECATED extends E_NOTICE
{
}
class E_USER_ERROR extends E_ERROR
{
}
class E_USER_WARNING extends E_USER_ERROR
{
}
class E_USER_DEPRECATED extends E_USER_WARNING
{
}
class E_USER_NOTICE extends E_USER_WARNING
{
}
