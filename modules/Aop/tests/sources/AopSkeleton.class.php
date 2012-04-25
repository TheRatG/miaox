<?php

class Miaox_AopSkeleton
{
	public function __construct()
	{
		
	}
	
	public function show1( $k = 0 )
	{
		echo '1';
		/// Pointcut: CustomPointcut_Pointcut1
		
		if ( $k != 0 )
		{
			/// Pointcut: CustomPointcut_PointcutIf
			echo '-k-';
		}
	}
	
	public function show2()
	{
		/// Pointcut: CustomPointcut_Pointcut1
		echo '2';
	}
	
	public function show3( $x = 0 )
	{
		$x++;
		/// Pointcut: CustomPointcut_Pointcut1
		
		if ( $x >= 2 )
		{
			/// Pointcut: CustomPointcut_PointcutIf
			$x++;
			return $x;
		}
		/// Pointcut: CustomPointcut_Pointcut2
		
		return $x;
	}
}

class Miaox_AopSkeletonSecond
{
	public function show1()
	{
		echo '4';
		/// Pointcut: CustomPointcut_Pointcut1
	}
	
	public function show2()
	{
		echo '3';
		/// Pointcut: CustomPointcut_Pointcut1
	}
}
