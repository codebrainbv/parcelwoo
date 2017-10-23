<?php

	$aConfigSettings = webshop::getDatabaseSettings();

	// Detect DB settings via configuration file
	if(is_array(($aConfigSettings)))
	{
		// MySQL Server/Host
		$aSettings['host'] = $aConfigSettings['db_host'];

		// MySQL Username
		$aSettings['user'] = $aConfigSettings['db_user'];

		// MySQL Password
		$aSettings['pass'] = $aConfigSettings['db_pass'];

		// MySQL Database name
		$aSettings['name'] = $aConfigSettings['db_name'];

		// MySQL Table Prefix
		$aSettings['prefix'] = $aConfigSettings['db_prefix'];

		// MySQL Library (MySQL or MySQLi)
		$aSettings['type'] = 'mysqli';
	}
	else
	{

		// MySQL Server/Host
		$aSettings['host'] = 'localhost';

		// MySQL Username
		$aSettings['user'] = '';

		// MySQL Password
		$aSettings['pass'] = '';

		// MySQL Database name
		$aSettings['name'] = '';

		// MySQL Table Prefix
		$aSettings['prefix'] = '';

		// MySQL Library (MySQL or MySQLi)
		$aSettings['type'] = 'mysqli';
	}

?>