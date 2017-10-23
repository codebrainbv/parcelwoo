<?php
	$sHtml = '';
	// [*] (stop deze code in iedere widget) 
	define('PARCELCHECKOUT_PATH', dirname(dirname(dirname(__DIR__))));
	require_once(PARCELCHECKOUT_PATH . '/php/parcelcheckout.php');
	$sImagePath = parcelcheckout_getRootUrl(2). 'images';
	$sLowerWidgetPath = parcelcheckout_getRootUrl(2). 'widgets/lower';

	$sHtml .= '
		<script type="text/javascript" src="js/wysihtml5-0.3.0.min.js"></script>
		<script type="text/javascript" src="js/parser_rules/advanced.js"></script>
		
		<script>
			function minimizeContactForm(){
				jQuery(\'.contactForm\').toggleClass(\'minimized\');
				$( \'.contactFormContent\' ).slideToggle(\'fast\');
			}
		</script>

		<link href="widgets/lower/css/contactForm.css" media="screen" rel="stylesheet" type="text/css">
		<div class="contactForm" id="contactForm">
		<div class="contactFormTopDeco widgetTopDeco brand-background-color">
		</div>
		<div class="contactFormTopInfo widgetTopInfo">
			Contact form
			<div class="minusLowerWidget" onclick="minimizeContactForm()">
				
			</div>
			<div class="infoIconLowerWidget" data-balloon="Verstuur een mail naar iDEAL CHECKOUT" data-balloon-pos="left">
				<img src="' . $sImagePath . '/info-icon.png" height="100%" >
			</div>
			<div class="moveIconLowerWidget noRightClick" data-balloon="houd ingedrukt om te verplaatsen" data-balloon-pos="left">
				<img src="' . $sImagePath . '/move-icon.png" height="100%" >
			</div>
		</div>
		<div onmousedown="disableLowerDragging()" onmouseup="enableLowerDragging()" ontouchstart="disableLowerDragging()" ontouchend="enableLowerDragging()" class="lowerWidgetContent contactFormContent">
	';
	// [\*]
	
	// [**] codeer hier wat er in de widget moet komen te staan.
	$sHtml .= '
		
	<form name="contactForm" method="post" action="' . $sLowerWidgetPath . '/contactFormMailScript.php">
	
		<div class="contactFormUserInfo">
			<table width="450px">
			<tr> 
			 
			 <td valign="top">
			  <input class="contactFormFName contactFormField" type="text" name="first_name" placeholder="*Voornaam:" maxlength="50" size="30">
			 </td>
			</tr>
			<tr>
			 
			 <td valign="top">
			  <input class="contactFormLName contactFormField" type="text" name="last_name" placeholder="*Achternaam:" maxlength="50" size="30">
			 </td>
			</tr>
			<tr>
			 
			 <td valign="top">
			  <input class="contactFormEMail contactFormField" type="text" name="email" placeholder="*E-mail:" value="" maxlength="80" size="30">
			 </td>
			</tr>
			<tr>
			 
			 <td valign="top">
			  <input class="contactFormTelephone contactFormField" type="text" name="telephone" placeholder=" Telefoon:" maxlength="30" size="30">
			 </td>
			</tr>
			<tr>
			 
			 <td valign="top">
			  <input class="contactFormSubject contactFormField" type="text" name="subject" placeholder="*Onderwerp:" maxlength="30" size="30">
			 </td>
			</tr>
			</table>
		</div>
	
		<div id="contactFormToolbar" style="display: none;">
			
		
			<a " data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h1"> <img class="contactFormEditIcon" src="' . $sImagePath . '/textEditIcons/h1.png" " ></a> 
			<a " data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h2"> <img class="contactFormEditIcon" src="' . $sImagePath . '/textEditIcons/h2.png" " ></a>  
		
			<a " data-wysihtml5-command="bold" title="CTRL+B"> <img class="contactFormEditIcon" src="' . $sImagePath . '/textEditIcons/bold.png" " ></a> 
			<a " data-wysihtml5-command="italic" title="CTRL+I"> <img class="contactFormEditIcon" src="' . $sImagePath . '/textEditIcons/italic.png" " ></a> 
			
			<a " data-wysihtml5-command="insertUnorderedList"> <img class="contactFormEditIcon" src="' . $sImagePath . '/textEditIcons/list.png" " ></a> 
			<a " data-wysihtml5-command="insertOrderedList"> <img class="contactFormEditIcon" src="' . $sImagePath . '/textEditIcons/list-1.png" " ></a> 
			
		<!--
			<a " data-wysihtml5-command="foreColor" data-wysihtml5-command-value="red"> <img class="contactFormEditIcon" src="' . $sImagePath . '/textEditIcons/red.png" " ></a> 
			<a " data-wysihtml5-command="foreColor" data-wysihtml5-command-value="green"> <img class="contactFormEditIcon" src="' . $sImagePath . '/textEditIcons/green.png" " ></a> 
			<a " data-wysihtml5-command="foreColor" data-wysihtml5-command-value="blue"> <img class="contactFormEditIcon" src="' . $sImagePath . '/textEditIcons/blue.png" " ></a> 
		-->
		
			<a " data-wysihtml5-command="undo"> <img class="contactFormEditIcon" src="' . $sImagePath . '/textEditIcons/undo.png" " ></a> 
			<a " data-wysihtml5-command="redo"> <img class="contactFormEditIcon" src="' . $sImagePath . '/textEditIcons/redo.png" " ></a> 
			
			<a " data-wysihtml5-command="createLink"> <img class="contactFormEditIcon" src="' . $sImagePath . '/textEditIcons/broken-link.png" " ></a> 
			<a " data-wysihtml5-command="insertImage"> <img class="contactFormEditIcon" src="' . $sImagePath . '/textEditIcons/folder.png" " ></a> 
			
			<div data-wysihtml5-dialog="createLink" style="display: none;">
			  <label>
				Link:
				<input data-wysihtml5-dialog-field="href" value="http://">
			  </label>
			  <a data-wysihtml5-dialog-action="save">OK</a>&nbsp;<a data-wysihtml5-dialog-action="cancel">Cancel</a>
			</div>
			
			<div data-wysihtml5-dialog="insertImage" style="display: none;">
			  <label>
				Image:
				<input data-wysihtml5-dialog-field="src" value="http://">
			  </label>
			  <label>
				Align:
				<select data-wysihtml5-dialog-field="className">
				  <option value="">default</option>
				  <option value="wysiwyg-float-left">left</option>
				  <option value="wysiwyg-float-right">right</option>
				</select>
			  </label>
			  <a data-wysihtml5-dialog-action="save">OK</a>&nbsp;<a data-wysihtml5-dialog-action="cancel">Cancel</a>
			</div>
			
		</div>
		  <textarea id="contactFormTextArea" name="comments" placeholder="*Bericht..."></textarea>
		  
		  <input class="contactFormResetBtn" type="reset" value="Reset mail!">
		  <input class="contactFormSubmitBtn" type="submit" value="Verzend">
	</form>

	<div id="log"></div>
	<script>
	
	  var editor = new wysihtml5.Editor("contactFormTextArea", {
		toolbar:        "contactFormToolbar",
		stylesheets:    "widgets/lower/css/contactForm.css",
		parserRules:    wysihtml5ParserRules
	  });
	  
	</script>
		
	';
	// [\**]
	
	// [*]
	$sHtml .= '
		</div>
		</div>
		
	';
	echo($sHtml);
	// [\*]
?>