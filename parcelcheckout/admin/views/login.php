<?php

	$sHtml = '';

	if(!empty($_SESSION['idealcheckout']['user']))
	{
		header('Location: index.php?view=dashboard');
		exit;
	}

	$sHashSalt = ''; // Some random string
	$aDatabaseSettings = parcelcheckout_getDatabaseSettings();

	if(empty($aDatabaseSettings['name']))
	{
		if(is_file(__DIR__ . '/install.php'))
		{
			header('Location: index.php?view=install');
			exit;
		}
		else
		{
			die('No database settings found!');
		}
	}

	$aFormValues = array('username' => '', 'password' => '');
	$aFormErrors = array('username' => false, 'password' => false);

	// See is session is properly started
	if(!empty($_SESSION['idealcheckout']['login:security_field']) && !empty($_SESSION['idealcheckout']['login:security_value']))
	{
		// Verify security post fields
		if(!empty($_POST['form']) && !empty($_POST[$_SESSION['idealcheckout']['login:security_field']]))
		{
			// See if form=login
			if(strcasecmp($_POST['form'], 'login') === 0)
			{
				if(strcasecmp($_POST[$_SESSION['idealcheckout']['login:security_field']], $_SESSION['idealcheckout']['login:security_value']) === 0)
				{
					if(empty($_POST['username']))
					{
						$aFormErrors['username'] = true;
						$sHtml .= '<div class="error">No valid username given.</div>';
					}
					elseif(empty($_POST['password']))
					{
						$aFormErrors['password'] = true;
						$sHtml .= '<div class="error">No valid password given.</div>';
					}
					else
					{
						$aFormValues['username'] = $_POST['username'];
						$aFormValues['password'] = $_POST['password'];
						// Encrypt password to test it in database

						$sEncryptedPassword = hash('sha256', $sHashSalt . $aFormValues['password']);
						$sql = "SELECT * FROM `" . $aDatabaseSettings['prefix'] . "parcelcheckout_users` WHERE (`username` = '" . parcelcheckout_escapeSql($aFormValues['username']) . "') AND (`password` = '" . parcelcheckout_escapeSql($sEncryptedPassword) . "') AND (`enabled` = '1') LIMIT 1;";
						if($_SESSION['idealcheckout']['user'] = parcelcheckout_database_getRecord($sql))
						{
							header('Location: index.php?view=dashboard');
							exit;
						}
						else
						{
							$aFormErrors['username'] = true;
							$aFormErrors['password'] = true;
							$sHtml .= '<div class="error">User or password not found.</div>';
						}
					}
				}
			}
		}
	}

	$_SESSION['idealcheckout']['login:security_field'] = parcelcheckout_getRandomCode(16);
	$_SESSION['idealcheckout']['login:security_value'] = parcelcheckout_getRandomCode(16);

	if(!in_array(true, $aFormErrors))
	{
		if(!empty($_GET['logout']))
		{
			$sHtml .= '<div class="success">U bent nu uitgelogd.</div>';
		}
	}

	$sHtml .= '
			<form class="login-form" action="index.php?view=login" method="post" name="login">
				<input name="form" type="hidden" value="login">
				<input name="' . htmlentities($_SESSION['idealcheckout']['login:security_field']) . '" type="hidden" value="' . htmlentities($_SESSION['idealcheckout']['login:security_value']) . '">
				<div class="username-wrapper' . ($aFormErrors['username'] ? ' error' : '') . '">
					<label for="username">Uw gebruikersnaam <em>*</em></label>
					<input id="username" name="username" type="text" value="' . htmlentities($aFormValues['username']) . '">
				</div>				<div class="password-wrapper' . ($aFormErrors['password'] ? ' error' : '') . '">
					<label for="password">Uw wachtwoord <em>*</em></label>
					<input id="password" name="password" type="password" value="' . htmlentities($aFormValues['password']) . '">
				</div>
				<div class="submit-wrapper">
					<input type="submit" value="Inloggen">
				</div>
			</form>';
	return $sHtml;
?>