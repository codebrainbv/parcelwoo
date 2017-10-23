<?php

class clsInt
{
	static public function toPercentage($fNonPercentage)
	{
		$iConvPercentage = 0;
		// $fNonPercentage = 0;
		
		$iConvPercentage = round($fNonPercentage * 100).'%';
		return $iConvPercentage;
	}


	
}



?>