<?php

	if(!empty($_SESSION['parcelcheckout']['user']))
	{
		unset($_SESSION['parcelcheckout']['user']);
	}

	header('Location: index.php?view=login&logout=1');
	exit;

?>