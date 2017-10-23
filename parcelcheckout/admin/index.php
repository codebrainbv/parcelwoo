<?php
	
	
	@ini_set('display_errors', 1);
	@ini_set('display_startup_errors', 1);
	// @error_reporting(E_ALL | E_STRICT);
	@error_reporting(E_ALL);
	
	
	define('PARCELCHECKOUT_PATH', dirname(__DIR__));
	require_once(PARCELCHECKOUT_PATH . '/php/parcelcheckout.php');
	
	
	$aDatabaseSettings = parcelcheckout_getDatabaseSettings();
	$sImagePath = parcelcheckout_getRootUrl(). 'images';
	
	
	// Load core library
	require_once('php/index.php');
	
	
	if(is_file(__DIR__ . '/debug.php'))
	{
		include_once(__DIR__ . '/debug.php');
	}
	
	
	session_set_cookie_params(3600, '/', '', false, true);
	session_start();
	
	if(!isset($_SESSION['parcelcheckout']))
	{
		$_SESSION['parcelcheckout'] = array();
	}
	
	
	if(empty($_SESSION['parcelcheckout']['user']))
	{
		$sHtml = '';
		// echo"user empty - ";
		
		$aFormValues = array('username' => '', 'password' => '');
		$aFormErrors = array('username' => false, 'password' => false);
		
		
		// echo"values and errors reset - ";
		
		// See is session is properly started
		if(!empty($_SESSION['parcelcheckout']['login:security_field']) && !empty($_SESSION['parcelcheckout']['login:security_value']))
		{
			// Verify security post fields
			if(!empty($_POST['form']) && !empty($_POST[$_SESSION['parcelcheckout']['login:security_field']]))
			{
				// See if form=login
				if(strcasecmp($_POST['form'], 'login') === 0)
				{
					if(strcasecmp($_POST[$_SESSION['parcelcheckout']['login:security_field']], $_SESSION['parcelcheckout']['login:security_value']) === 0)
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
							if($_SESSION['parcelcheckout']['user'] = parcelcheckout_database_getRecord($sql))
							{
								header('Location: index.php');
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
			
		$_SESSION['parcelcheckout']['login:security_field'] = parcelcheckout_getRandomCode(16);
		$_SESSION['parcelcheckout']['login:security_value'] = parcelcheckout_getRandomCode(16);

		
		$sHtml .= '<!doctype html>
		<html style="height: 100%">
			<head>
				<link href="css/fonts.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/login.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/branding.css" media="screen" rel="stylesheet" type="text/css">
				<script>
				';
					$backgroundNumber = rand(1, 8);
				$sHtml .='	
				</script>
				<script>
					function showDisclaimer(){
						if (document.getElementById("disclaimer-text").style.display == "none"){
							document.getElementById("disclaimer-text").style.display = "block";
						}else{
							document.getElementById("disclaimer-text").style.display = "none";
						}
					}
				</script>
			</head>
			<body style="background-image: url(\'' . $sImagePath . '/backgrounds/' . $backgroundNumber . '.jpg\')">
				<div class="content">
					<div class="logIn-header-logo"></div>
					
					<div class="login-form-div">
						<form class="login-form" action="" method="post" name="login">
							<input name="form" type="hidden" value="login">
							<input name="' . htmlentities($_SESSION['parcelcheckout']['login:security_field']) . '" type="hidden" value="' . htmlentities($_SESSION['parcelcheckout']['login:security_value']) . '">
							
							<div class="username-wrapper' . ($aFormErrors['username'] ? ' error' : '') . '">
								<input id="username" name="username" placeholder="E-mailadres" type="text" value="' . htmlentities($aFormValues['username']) . '">
							</div>				
							<div class="password-wrapper' . ($aFormErrors['password'] ? ' error' : '') . '">
								<input id="password" name="password" placeholder="wachtwoord" type="password" value="' . htmlentities($aFormValues['password']) . '">
								<input class="login-icon" type="submit" value="">
							</div>
						</form>
					</div>
					<div class="login-bottom-text">
						<a class="login-bottom-link" href="https://www.ideal-checkout.nl/">Payments for Websites & Webshops</a><br>
						<a class="login-bottom-disclaimer" onclick="showDisclaimer()"> Disclaimer </a>
						
						
					</div>
					<div id="disclaimer-text" style="display: none;">
							
							De handelsmerken, handelsnamen, beelden, logo\'s die de producten en diensten van www.ideal-checkout.nl herkenbaar maken, alsmede het ontwerp, tekst en grafische mogelijkheden van de website zijn het eigendom van CodeBrain.<br> Tenzij dit uitdrukkelijk is bepaald, zal niets van hetgeen hierin is vervat, worden uitgelegd als het verlenen van een licentie of recht uit hoofde van het auteursrecht of enig ander intellectueel eigendomsrecht van CodeBrain, Alle rechten voorbehouden.<br> Anders is bepaald voor alle foto\'s gebruikt binnen deze website, deze vallen allen onder Fair Use en Creative Commons Zero (CC0) license zoals bepaald en verstrekt op Pexels.com.<br>
					</div> 
				</div>
			</body>
		</html>';
		
		echo $sHtml;
	
	}
	elseif(!preg_match('/^([a-z0-9\-]+)$/', "index.php") || !is_file($sViewPath . "index.php" . '.php'))
	{

		$iPremiumUser = $_SESSION['parcelcheckout']['user']['premium'];
		$sUsername = $_SESSION['parcelcheckout']['user']['username'];
		$sUsernameMd5 = md5($sUsername);
		if (strpos($sUsername, '@') !== false){
			$sUsernameCleaned = substr($sUsername, 0, strpos($sUsername, "@"));
		}else{
			$sUsernameCleaned = $sUsername;
		}
		
			
		header('Content-Type: text/html; charset=UTF-8');
			
		$sHtml = '
		<!doctype html>
		<html style="height: 100%">
			<head>
				<title>iDEAL Dashboard</title>
				
				<meta http-equiv="content-type" content="text/html; charset=UTF-8">
				<meta http-equiv="content-language" content="nl-nl">
				<meta name="robots" content="index, follow">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">

				<script type="text/javascript">
					var sUsernameMd5 = "' . $sUsernameMd5 . '";	
				</script>
				
				<link href="js/jquery-ui.css" media="screen" rel="stylesheet" type="text/css">
				<link href="js/jquery-ui.theme.min.css" media="screen" rel="stylesheet" type="text/css">
			
				<link href="css/fonts.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/header.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/sidebar.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/content.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/widgets.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/footer.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/body.css" media="screen" rel="stylesheet" type="text/css">
				
				<link href="css/layout.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/branding.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/settingsdialog.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/messagedialog.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/profilemenu.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/notificationmenu.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/infodialog.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/balloon.css" media="screen" rel="stylesheet" type="text/css">
				<link href="css/responsive.css" media="screen" rel="stylesheet" type="text/css">
				
				<meta name="viewport" content="width=device-width, initial-scale=1.0">	
				<script type="text/javascript" src="js/jquery-2.2.0.min.js"></script>
				<script type="text/javascript" src="js/jquery-ui.min.js"></script>
				<script type="text/javascript" src="js/scripts.js"></script>
				<script type="text/javascript" src="js/Chart.js"></script>
				<script type="text/javascript" src="js/widgets.js"></script>
				<script type="text/javascript" src="js/jquery.cookie.js"></script>
				
				<script type="text/javascript" src="js/jquery.ui.touch-punch.min.js"></script>  <!-- zorgt ervoor dat jquery ui werkt op mobiele apparaten mbt slepen ed -->

				<script type="text/javascript">
				
					$(document).ready(function() {
						is_screen_larger_1200()
						is_screen_600_1200();
						is_screen_less_600();
					});

					$(window).resize(function() {
						
						is_screen_less_600();
					});
				
					function is_screen_larger_1200()
					{
						var iWidth = $(document).width();
						
						if( iWidth > 1200 ){
							jQuery(\'.header-logo\').toggleClass(\'mobile\'); 
							
							jQuery(\'.sidebar\').toggleClass(\'sidebar-thin\');
							jQuery(\'.content\').toggleClass(\'wide-width-content\');
							
							setTimeout( function(){ for(k in Chart.instances) { Chart.instances[k].resize(); Chart.instances[k].render(); } }, 1100);
						}
					}
				
					function is_screen_600_1200()
					{
						var iWidth = $(document).width();
						
						if( iWidth <= 1200 && iWidth >= 600 ){
							jQuery(\'.header-logo\').addClass(\'mobile\'); 
							jQuery(\'#sidebar\').slideUp(\'slow\');
							jQuery(\'.content\').addClass(\'full-width-content\');
						}
					}
					
					function is_screen_less_600()
					{
						var iWidth = $(document).width();
						
						if( iWidth <= 600 ){
							jQuery(\'.thin-menu-icon\').css(\'display\', \'none\'); 
						}else{
							jQuery(\'.thin-menu-icon\').css(\'display\', \'block\'); 
						}
					}
				</script>
				<script type="text/javascript">
					function toggle_header_info()
					{
						jQuery(\'.pspInfoContainer\').toggleClass(\'minimized\');
						jQuery(\'#info-header\').slideToggle(\'fast\');
					}
					function hideTopWidget(){
						jQuery(\'.topWidgetContainer\').toggleClass(\'minimized\');
						jQuery(\'.content-row-upper\').slideToggle(\'fast\');
					}
				</script>
				<script>
					function openSettingsDialog(){
						$( \'#settingsDialog\' ).toggle("slide", { direction: "right" });
					}
					
					function openProfileMenu(){
						$(\'#profileMenu\').slideToggle(\'fast\');
					}
					function openNotificationMenu(){
						$(\'#notificationMenu\').slideToggle(\'fast\');
					}
					
					function openMessageDialog(){
						$( \'#messageDialog\' ).toggle("slide", { direction: "right" });
					}
					
					function openInfoDialog(){
						$( \'#infoDialog\' ).toggle("slide", { direction: "right" });
					}
					
					$( function() {
						$( "#headerInfoAccordion" ).accordion({
							collapsible: true,
							heightStyle: "content"
						});
					});
					
					function openHelpDialog(){
						$( \'#helpDialog\' ).dialog();
					}
				</script>
			
				<script>
					function disablePspInfo(){
						$(\'.pspInfoContainer\').slideToggle(\'fast\');
						$(\'.disablePspInfoButton\').slideToggle(\'fast\');
					}
					
					function disableTopWidget(){
						$(\'.topWidgetContainer\').slideToggle(\'fast\');
						$(\'.disableTopWidgetButton\').slideToggle(\'fast\');
					}
				</script>
				<script>
					Chart.defaults.global.defaultFontColor = "#686868";
					Chart.defaults.global.defaultFontFamily = "OpenSansRegular";
					Chart.defaults.global.defaultFontSize = 14;
					
				</script>
				<script>
					setTimeout( function(){ 
							$(\'.noRightClick\').bind(\'contextmenu\', function(e) {
								return false;
							}); }, 1200);
							
					function disableRightClick(){
						setTimeout( function(){ 
							$(\'.noRightClick\').bind(\'contextmenu\', function(e) {
								return false;
							}); }, 500);
					}
				</script>
				<script>
					function logOut(){
						window.location.replace("logout.php");
					}
				</script>
				<script>
					function activateHeaderMenuBtn(){
						
						if($(\'.sidebar\').hasClass(\'sidebar-thin\')) 
						{
							jQuery(\'.sidebar-thin\').slideToggle(\'slow\');
							jQuery(\'.content\').toggleClass(\'wide-width-content\');
						}else{
							jQuery(\'.header-logo\').toggleClass(\'mobile\');
							jQuery(\'.sidebar\').slideToggle(\'slow\');							
						}
						
						jQuery(\'.content\').toggleClass(\'full-width-content\');
						
						setTimeout( function(){ for(k in Chart.instances) { Chart.instances[k].resize(); Chart.instances[k].render(); } }, 1100);
					}
					function mobileWidgetSelect(){
						var iWidth = $(document).width();
						
						if( iWidth <= 600 ){
							if(document.getElementById("sidebar").style.height == "1px"){
								document.getElementById("sidebar").style.height = "auto";
							}else{
								document.getElementById("sidebar").style.height = "1px";
							}
						}
					}
				</script>
				
			</head>
			<body onload="is_screen_600_1200();" style="min-height: 100%;">
				<header>
					<div class="header-logo"></div>
					<div class="header-menu" onclick="activateHeaderMenuBtn();">
						<img class="menu-icon icon" height="20" width="20" style="cursor: pointer; cursor: hand;" src="' . $sImagePath . '/menu-icon.png">
					</div>
					
					
					
					<div class="header-account">
					
					<!--
						<div class="header-account-section">
							
						</div>
					-->
						<div class="header-account-section" >
							<div class="header-account-section-icon" onclick="openProfileMenu()">
								<img class="profile-icon icon"  height="22" width="22" src="' . $sImagePath . '/profile-icon.png">
							</div>
							<div id="profileMenu" class="profileMenu" style="display: none">
								<div class="profile">
									
									<div class="profile-menu-icon-holder" title="instellingen" onclick="openSettingsDialog()">
										<img class="profile-menu-icon icon" src="' . $sImagePath . '/settings-icon.png">
									</div>
									<div id="settingsDialog" class="settingsDialog" style="display: none;"> 
										<B>INSTELLINGEN</B>
										<div class="closeHeaderAccSectionBtn" onclick="openSettingsDialog()">
											<img src="' . $sImagePath . '/cross-icon.png" height="100%" >
										</div>
										<br>
										<button onclick="resetTopWidgets()">Reset top widgets</button>
										<button onclick="resetLowerWidgets()">Reset lower widgets</button>
									</div>
									
									';
									
									if ((date('N') <= 5)&&(date('G') >= 9)&&(date('G') <= 12))
									{
										$sHtml .='
										<div class="profile-menu-icon-holder" title="iDEAL Checkout">
											<a href="tel:+31522746060"><img class="profile-menu-icon icon" src="' . $sImagePath . '/phone-icon.png"></a>
										</div>
										';
									}else{
										$sHtml .='
										<div class="profile-menu-icon-holder" title="iDEAL Checkout" onclick="alert(\'iDEAL Checkout is telefonisch bereikbaar op werkdagen van 09:00/12:00\');">
											<img class="profile-menu-icon icon" src="' . $sImagePath . '/phone-forbid-icon.png">
										</div>
										';
									}
									$sHtml .='
									
									<a href="mailto: ">
									<div class="profile-menu-icon-holder" title="iDEAL Checkout" onclick="">
										<img class="profile-menu-icon icon" src="' . $sImagePath . '/messages-icon.png" id="messages-icon">
									</div>
									</a>
									
									<a href="https://twitter.com/parcelcheckout" target="_blank">
									<div class="profile-menu-icon-holder" title="iDEAL Checkout" onclick="">
										<img class="profile-menu-icon icon" src="' . $sImagePath . '/twitter-icon.png">
									</div>
									</a>
									
									<!--
									<div class="profile-menu-icon-holder" onclick="">
										<img class="profile-menu-icon icon" src="' . $sImagePath . '/inbox-icon.png">
									</div>
									-->
									
									<a href="https://www.ideal-checkout.nl/faq-ic" target="_blank">
									<div class="profile-menu-icon-holder" title="FAQ" onclick="">
										<img class="profile-menu-icon icon" src="' . $sImagePath . '/search-icon.png">
									</div>
									</a>
									
									<div class="profile-menu-icon-holder" title="uitloggen" onclick="logOut()">
										<img class="profile-menu-icon icon" src="' . $sImagePath . '/logOut-icon.png">
									</div>
									
								</div>
							</div>
						</div>
						
						<div class="header-account-section" >
							<div class="header-account-section-icon" onclick="openNotificationMenu()">
								<img class="notification-icon icon"  height="22" width="22" src="' . $sImagePath . '/bell-icon.png">
							</div>
							<div id="notificationMenu" class="notificationMenu" style="display: none">
								<div class="notification">
									
									<div class="profile-menu-icon-holder" title="nieuws" onclick="openMessageDialog()">
										<img class="profile-menu-icon icon" src="' . $sImagePath . '/bullhorn-icon.png">
									</div>
									<div id="messageDialog" class="messageDialog" style="display: none">
										<div class="news">
											<div class="news-section-title">
												<b>NIEUWS</b>
												<div class="closeHeaderAccSectionBtn" onclick="openMessageDialog()">
													<img src="' . $sImagePath . '/cross-icon.png" height="100%" >
												</div>
											</div>
											'; 
											
											$sNewsData = file_get_contents('http://graphxdemo.nl/newsapi');
											//print_r($sNewsData);
											$aNewsData = json_decode($sNewsData, true);
											// print_r($aNewsData);
											
											foreach($aNewsData as $aItem){
											$sHtml .= '
												<div class="news-wrapper">
													<div class="news-title"><b>' . htmlspecialchars($aItem['title']) . '</b></div>
													<div class="news-description">' . htmlspecialchars($aItem['description']) . '</div>
													<div class="news-timestamp"><sub>' . date('d-m-Y', $aItem['timestamp']) . ' <a href="https://www.ideal-checkout.nl">meer info</a></sub></div>
												</div>';
												if($aItem['timestamp'] >= strtotime('-1 day', time()) ){
													$sHtml .= '
														<script>
															document.getElementById("messages-icon").src = "' . $sImagePath . '/new-messages-icon.png";
														</script>
													';
												}
											}
											$sHtml .= '
										</div>
									</div>
									
									<a href="https://www.ideal-checkout.nl/faq-ic/algemeen/ideal-checkout-builds" target="_blank">
									<div class="profile-menu-icon-holder" title="updates" onclick="">
										<img class="profile-menu-icon icon" src="' . $sImagePath . '/flag-icon.png">
									</div>
									</a>
									
									<div class="profile-menu-icon-holder" title="live helpdesk" onclick="">
										<img class="profile-menu-icon icon" src="' . $sImagePath . '/speech-icon.png">
									</div>
									
								</div>
							</div>
						</div>
						
						<div class="header-account-section">
							<div class="header-account-section-icon" onclick="openInfoDialog()">
								<img class="info-icon icon" height="22" width="22" src="' . $sImagePath . '/bulb-icon.png">
							</div>
							<div id="infoDialog" class="infoDialog" title="Berichten" style="display: none">
								<div class="info">
									<div class="info-section-title">
										<b>INFORMATIE</b>
										<div class="closeHeaderAccSectionBtn" onclick="openInfoDialog()">
											<img src="' . $sImagePath . '/cross-icon.png" height="100%" >
										</div>
									</div>
									<div id="headerInfoAccordion">
										<h3>Ideal dashboard</h3>
										<div>
											<p>Welkom bij het Ideal checkout dashboard, in dit dashboard kan je ultrices a, suscipit eget, quam. Integer ut neque. Vivamus nisi metus, molestie vel, gravida in, condimentum sit amet, nunc.</p>
										</div>
										<h3>Help mijn widgets zijn leeg!</h3>
										<div> 
											<p>Wanneer uw widgets leeg zijn hoeft u zich geen zorgen te maken.<br>
											Sommige widgets halen data per maand op (jan, feb, mrt, etc).<br>
											Wanneer er deze maand data gegenereerd is (met een verkoop) zal deze verschijnen in het widget.<br>
											Bekijk het i *info icoontje om te zien of het widget per maand data ophaalt.</p>
										</div>
										<h3>PSP & iDEAL Checkout informatie tonen</h3>
										<div> 
											<p>Sed non urna. Donec et ante. Phasellus eu ligula. Vestibulum sit amet purus. Vivamus hendrerit, dolor at aliquet laoreet, mauris turpis porttitor velit, faucibus interdum tellus libero ac justo. Vivamus non quam. In suscipit faucibus urna. </p>
										</div>
										<h3>Top widgets verbergen</h3>
										<div>
											<p>Nam enim risus, molestie et, porta ac, aliquam ac, risus. Quisque lobortis. Phasellus pellentesque purus in massa. Aenean in pede. Phasellus ac libero ac tellus pellentesque semper. Sed ac felis. Sed commodo, magna quis lacinia ornare, quam ante aliquam nisi, eu iaculis leo purus venenatis dui. </p>
											<ul>
												<li>List item one</li>
												<li>List item two</li>
												<li>List item three</li>
											</ul>
										</div>
										<h3>Widgets toevoegen</h3>
										<div>
											<p>Cras dictum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aenean lacinia mauris vel est. </p><p>Suspendisse eu nisl. Nullam ut libero. Integer dignissim consequat lectus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. </p>
										</div>
										<h3>Hoe dit</h3>
										<div>
											<p>Cras dictum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aenean lacinia mauris vel est. </p><p>Suspendisse eu nisl. Nullam ut libero. Integer dignissim consequat lectus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. </p>
										</div>
										<h3>Hoe dat</h3>
										<div>
											<p>Cras dictum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aenean lacinia mauris vel est. </p><p>Suspendisse eu nisl. Nullam ut libero. Integer dignissim consequat lectus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. </p>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						
						
					</div>
				</header>
				<div class="body">
					<div class="sidebar" id="sidebar">
						<ul>';
						
						
			if(false) //empty($_SESSION['parcelcheckout']['user']))
			{
				$sHtml .= '
								<li><a href="index.php?view=login"><img class="login-icon icon" height="32" width="32" src="' . $sImagePath . '/logout-icon.png"><span class="text">Login</span></a></li>						
								<li id="menu-toggle" onclick="javascript: jQuery(\'#sidebar\').toggleClass(\'sidebar-thin\');"><img class="menu-icon icon" height="32" width="32" src="' . $sImagePath . '/menu-icon.png"><span class="text">Menu invouwen</span></li>';
			}
			else
			{
				
				$sHtml .= '
				<ul class="sidebar-menu">
					<div class="sidebar-header wideMenuText">WIDGETS</div>
					<li class="treeview closed" id="menu-tree">
					  <a href="#">
						<span class="wideMenuText">Top Widgets</span>
						<img class="treeviewArrowIcon" src="' . $sImagePath . '/treeview-menu-icon.png">
						<img class="thinMenuIcon" height="20" width="20" src="' . $sImagePath . '/top-widgets-icon.png">
					  </a>
					  <ul class="treeview-menu">
					  
						<li>
							<div id="topwidget1-topDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="index0.html"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-red.png"> topWidget v1 R</a>
							</div>
						</li>
						<li>
							<div id="currencyconverter-topDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="index2.html"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-blue.png"> currencyconverter</a>
							</div>
						</li>
						<li>
							<div id="topwidget3-topDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="index3.html"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-purple.png"> topWidget v3 P</a>
							</div>
						</li>
						<li>
							<div id="topwidget4-topDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="index4.html"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-white.png"> topWidget v4 W</a>
							</div>
						</li>
						<li>
							<div id="topwidget5-topDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="index3.html"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-green.png"> topWidget v5 G</a>
							</div>
						</li>
						<li>
							<div id="topwidget6-topDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="index4.html"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-orange.png"> topWidget v6 O</a>
							</div>
						</li>
						<li>
							<div id="topwidget7-topDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="index5.html"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-yellow.png"> topWidget v7 y</a>
							</div>
						</li>
						
					  </ul>
					</li>
					
					<li class="treeview closed" id="menu-tree2">
					  <a href="#">
						<span class="wideMenuText">Flow Charts</span>
						<img class="treeviewArrowIcon" src="' . $sImagePath . '/treeview-menu-icon.png">
						<img class="thinMenuIcon" height="20" width="20" src="' . $sImagePath . '/flowchart-icon.png">
					  </a>
					  <ul class="treeview-menu">
						<li>
							<div id="ordersLine-lowerDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="#"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-blue.png"> Orders line-chart</a>
							</div>
						</li>
						
						<li> 
							<div id="lowerWidget7-lowerDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="#"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-yellow.png"> lowerWidget v6 y</a>
							</div>
						</li>
						
						</ul>
					</li>
					
					<li class="treeview closed" id="menu-tree3">
					  <a href="#">
						<span class="wideMenuText">Donut Charts</span>
						<img class="treeviewArrowIcon" src="' . $sImagePath . '/treeview-menu-icon.png">
						<img class="thinMenuIcon" height="20" width="20" src="' . $sImagePath . '/donutchart-icon.png">
					  </a>
					  <ul class="treeview-menu">
						<li>
							<div id="methodsDonut-lowerDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="#"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-purple.png"> Methoden donut</a>
							</div>
						</li>
						<li>
							<div id="methodsPercentDonut-lowerDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="#"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-purple.png"> Methoden %donut</a>
							</div>
						</li>
					  </ul>
					</li>
					
					<li class="treeview closed" id="menu-tree4">
					  <a href="#">
						<span class="wideMenuText">Bar Charts</span>
						<img class="treeviewArrowIcon" src="' . $sImagePath . '/treeview-menu-icon.png">
						<img class="thinMenuIcon" height="20" width="20" src="' . $sImagePath . '/barchart-icon.png">
					  </a>
					  <ul class="treeview-menu">
						<li> 
							<div id="statsChart-lowerDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="#"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-green.png"> Status chart</a>
							</div>
						</li>
						<li>
							<div id="methodsHoriChart-lowerDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="#"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-purple.png"> Methoden %chart</a>
							</div>
						</li>
						<li>
							<div id="periodTransactions-lowerDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="#"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-green.png"> Transacties</a>
							</div>
						</li>
					  </ul>
					</li>
					
					<li class="treeview closed" id="menu-tree5">
					  <a href="#">
						<span class="wideMenuText">mappen</span>
						<img class="treeviewArrowIcon" src="' . $sImagePath . '/treeview-menu-icon.png">
						<img class="thinMenuIcon" height="20" width="20" src="' . $sImagePath . '/maps-icon.png">
					  </a>
					  <ul class="treeview-menu">
						<li>
							<div id="ordersWorldMap-lowerDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="#"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-green.png"> Orders wereld map</a>
							</div>
						</li>
						<li>
							<div id="ordersEuMap-lowerDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="#"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-green.png"> Orders Europa map</a>
							</div>
						</li>
						<li>
							<div id="ordersNetherlandsMap-lowerDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="#"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-green.png"> Orders Nederland map</a>
							</div>
						</li>
					  </ul>
					</li>
					
					<li class="treeview closed" id="menu-tree6">
					  <a href="#">
						<span class="wideMenuText">Misc</span>
						<img class="treeviewArrowIcon" src="' . $sImagePath . '/treeview-menu-icon.png">
						<img class="thinMenuIcon" height="20" width="20" src="' . $sImagePath . '/misc-icon.png">
					  </a>
					  <ul class="treeview-menu">';
						if($iPremiumUser == 1){ //alleen weergeven bij premium users
							$sHtml .= '
							<li>
								<div id="orders-pr-lowerDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
									<a href="#"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-blue.png"> Orders</a>
								</div>
							</li>'
						;}else{ //alleen weergeven bij non-premium users
							$sHtml .= ' 
							<li>
								<div id="orders-lowerDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
									<a href="#"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-blue.png"> Orders</a>
								</div>
							</li>';
						}
						$sHtml .= ' 
						
					  </ul>
					</li>
					
					<li class="treeview closed" id="menu-tree7">
					  <a href="#">
						<span class="wideMenuText">Extra</span>
						<img class="treeviewArrowIcon" src="' . $sImagePath . '/treeview-menu-icon.png">
						<img class="thinMenuIcon" height="20" width="20" src="' . $sImagePath . '/extra-icon.png">
					  </a>
					  <ul class="treeview-menu">
						<li>
							<div id="contactForm-lowerDraggable" ontouchstart="mobileWidgetSelect();" ontouchend="mobileWidgetSelect();">
								<a href="#"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-orange.png"> Contact form</a>
							</div>
						</li>
					  </ul>
					</li>
					
					<div class="sidebar-header wideMenuText">EXTRA</div>
					
					<li class="treeview closed" id="menu-tree7">
					  <a href="https://www.ideal-checkout.nl/faq-ic/algemeen/ideal-checkout-builds" target="_blank">
						<span class="wideMenuText">Changelog Archief</span>
						<img class="thinMenuIcon" height="20" width="20" src="' . $sImagePath . '/archive-icon.png">
					  </a>
					</li>
					
					<li class="treeview closed" id="menu-tree8">
					  <a href="https://www.ideal-checkout.nl/ssl" target="_blank">
						<span class="wideMenuText">SSL Aanschaffen</span>
						<img class="thinMenuIcon" height="20" width="20" src="' . $sImagePath . '/ssl-icon.png">
					  </a>
					</li>
					
					<li class="treeview closed" id="menu-tree9">
					  <a href="https://www.ideal-checkout.nl/over-ons/donatie" target="_blank">
						<span class="wideMenuText">Doneren</span>
						<img class="thinMenuIcon" height="20" width="20" src="' . $sImagePath . '/coffee-icon.png">
					  </a>
					</li>
					
					<li class="treeview closed" id="menu-tree10">
					  <a href="#">
						<span class="wideMenuText">Premium</span>
						<img class="treeviewArrowIcon" src="' . $sImagePath . '/treeview-menu-icon.png">
						<img class="thinMenuIcon" height="20" width="20" src="' . $sImagePath . '/premium-icon.png">
					  </a>
					  <ul class="treeview-menu">
						<li><a href="pages/tables/simple.html"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle.png"> Voordelen</a></li>
						<li><a href="pages/tables/data.html"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle.png"> Kopen</a></li>
					  </ul>
					</li>
					
					<li class="treeview closed" id="menu-tree11">
					  <a href="https://www.ideal-checkout.nl/over-ons/reviews" target="_blank">
						<span class="wideMenuText">Reviews</span>
						<img class="thinMenuIcon" height="20" width="20" src="' . $sImagePath . '/star-icon.png">
					  </a>
					</li>
					
					<div class="sidebar-header wideMenuText">SECTIES</div>
					
					<li><a href="#" class="disablePspInfoButton labelsButton" onclick="disablePspInfo()" style="display:none;"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-red.png"> <span class="wideMenuText">CONTACT</span></a></li>
					
					<li><a href="#" class="disableTopWidgetButton labelsButton" onclick="disableTopWidget()" style="display:none;"><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-white.png"> <span class="wideMenuText">TOP WIDGETS</span></a></li>
					
					<li><a href="#" class="labelsButton" onclick="openHelpDialog()" ><img class="circle-icon icon" height="36" width="36" src="' . $sImagePath . '/circle-blue.png">
							<span class="wideMenuText">Placeholder</span> 
							<div id="helpDialog" title="Help" style="display: none">
								<p>This is the default dialog which is useful for displaying information. The dialog window can be moved, resized and closed with the icon.</p>
							</div>
						</a>
					</li>
					
					<li>
						<div class="thin-menu-icon-holder" onclick="javascript: 
							jQuery(\'.header-logo\').toggleClass(\'mobile\'); 
							
							jQuery(\'.sidebar\').toggleClass(\'sidebar-thin\');
							jQuery(\'.content\').toggleClass(\'wide-width-content\');
							
							setTimeout( function(){ for(k in Chart.instances) { Chart.instances[k].resize(); Chart.instances[k].render(); } }, 1100);
							">
							<img class="thin-menu-icon icon" src="' . $sImagePath . '/thin-menu-icon.png">
						</div>
					</li>
			  </ul>';
				
			}
			$sHtml .= '
						</ul>
					</div>
					
					<div class="content">
						<div class="content-header" id="content-header" >
							<div style="overflow: hidden;">
								<div class="content-header-left">Welkom '. $sUsernameCleaned .'</div> 
								<div class="content-header-right">' . date("d-m-Y") . '</div>
							</div>
						<div class="pspInfoContainer minimized">
							<div class="pspInfoTopDeco brand-background-color">
							</div>
							<div class="pspInfoTopInfo" >
								<b>Contact informatie</b>
								<div class="minusLowerWidget" onclick="toggle_header_info()"></div>
								<div class="crossPspInfo minusLowerWidget" onclick="disablePspInfo()"><img src="' . $sImagePath . '/cross-icon.png" height="100%" ></div>
							</div>
									<div class="info-header" id="info-header"> 
									
										<img class="header-psp-logo" src="' . $sImagePath . '/example-psp.png"> 
										<div class="header-producten"> 
											<b>Producten</b> <br> 
											Rabo Omnikassa 
										</div>
										<div class="header-config-dashboard"> 
											<b>Config Dashboard</b> <br>
											Voor vragen of klachten over Rabo Omnikassa. <br>
											Tel: +31 (030) 712 21 17 <br>
											Bereikbaar: werkdagen van 8.00 tot 17.30 uur. <br>
											E-mail: contact@omnikassa.rabobank.nl
										</div>
										<div class="header-support"> 
											<b>Support</b> <br>
											Voor vragen of klachten over Rabo Omnikassa. <br>
											Tel: +31 (030) 712 21 17 <br>
											Bereikbaar: werkdagen van 8.00 tot 17.30 uur. <br>
											E-mail: contact@omnikassa.rabobank.nl
										</div>
										<div class="header-support"> 
											<b>Support</b> <br>
											Voor vragen of klachten over Rabo Omnikassa. <br>
											Tel: +31 (030) 712 21 17 <br>
											Bereikbaar: werkdagen van 8.00 tot 17.30 uur. <br>
											E-mail: contact@omnikassa.rabobank.nl
										</div>
									</div>
									
							</div>	
						</div>
						<div class="content-section">
							<div class="topWidgetContainer">
								<div class="topWidgetsTopDeco">
								</div>
								<div class="topWidgetsTopInfo" >
									<b> Top widgets </b>
									<div class="minusLowerWidget" onclick="hideTopWidget()"></div>
									<div class="crossTopWidget minusLowerWidget" onclick="disableTopWidget()"><img src="' . $sImagePath . '/cross-icon.png" height="100%" ></div>
								</div>
								<div class="content-row content-row-upper" id="content-row-upper-col1">
									<div class="content-col" id="col1-topDroppable"></div>
									<div class="content-col" id="col2-topDroppable"></div>
									<div class="content-col" id="col3-topDroppable"></div>
									<div class="content-col" id="col4-topDroppable"></div>
								</div>
							</div>';
							
							if($iPremiumUser == 1){
								$sHtml .= '
								<div class="content-row content-row-lower">
									<div class="content-col content-row-lower-col" id="content-row-lower-col1">
										<div class="content-bigCol" id="bigCol1-lowerDroppable"></div>
										<div class="content-bigCol" id="bigCol2-lowerDroppable"></div>
										<div class="content-bigCol" id="bigCol3-lowerDroppable"></div>
										<div class="content-bigCol" id="bigCol4-lowerDroppable"></div>
										<div class="content-bigCol" id="bigCol5-lowerDroppable"></div>
										<div class="cleared"></div>
									</div>
									<div class="content-col content-row-lower-col" id="content-row-lower-col2">
										<div class="content-bigCol" id="bigCol6-lowerDroppable"></div>
										<div class="content-bigCol" id="bigCol7-lowerDroppable"></div>
										<div class="content-bigCol" id="bigCol8-lowerDroppable"></div>
										<div class="content-bigCol" id="bigCol9-lowerDroppable"></div>
										<div class="content-bigCol" id="bigCol10-lowerDroppable"></div>
										<div class="cleared"></div>
									</div>
									<div class="cleared"></div>
								</div>';
							}else{
								$sHtml .= '
								<div class="content-row content-row-lower">
									<div class="content-col content-row-lower-col" id="content-row-lower-col1">
										<div class="content-bigCol" id="bigCol1-lowerDroppable"></div>
										<div class="content-bigCol" id="bigCol2-lowerDroppable"></div>
										<div class="content-bigCol" id="bigCol3-lowerDroppable"></div>
										<div class="cleared"></div>
									</div>
									<div class="content-col content-row-lower-col" id="content-row-lower-col2">
										<div class="content-bigCol" id="bigCol6-lowerDroppable"></div>
										<div class="content-bigCol" id="bigCol7-lowerDroppable"></div>
										<div class="content-bigCol" id="bigCol8-lowerDroppable"></div>
										<div class="cleared"></div>
									</div>
									<div class="cleared"></div>
								</div>';
							}
							
							$sHtml .= '
						</div>			
					</div>
					<div class="cleared"></div>
				</div>
				
				<footer>

					<div class="footer-copyright footer-left">
						<a href="http://www.ideal-checkout.nl" target="_blank">&copy; http://www.ideal-checkout.nl</a>
					</div>
					<div class="footer-pluginversion footer-right">
						Dashboard v0.9.6
					</div>
				</footer>
				
				<script type="text/javascript">
				$(\'.treeview\').click(function(e) 
				{
					if($(this).hasClass(\'active\')) 
					{
						$(\'.treeview.active .treeview-menu\').slideUp(\'fast\');
						$(this).removeClass(\'active\').addClass(\'closed\');
					} 
					else 
					{
						$(\'.treeview.active .treeview-menu\').slideUp(\'fast\');
						$(this).siblings().removeClass(\'active\').addClass(\'closed\'); 
						$(this).removeClass(\'closed\').addClass(\'active\');
						$(\'.treeview.active .treeview-menu\').slideDown(\'fast\');
					}
				});
				
				</script>
				
			</body>
		</html>';

		echo $sHtml;
		exit;
			
	}
	
	
?>