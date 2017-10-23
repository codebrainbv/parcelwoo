<?php

	if(!empty($_SESSION['idealcheckout']['user']))
	{
		unset($_SESSION['idealcheckout']['user']);
	}

	header('Location: index.php?view=login&logout=1');
	exit;

?>