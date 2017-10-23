<?php
if(isset($_POST['email'])) {
 
    // EDIT AS REQUIRED
    $email_to = "support@ideal-checkout.nl";
    $email_subject = "iDeal dashboard contactForm Widget";
 
    function died($error) {
        // error code
		echo '<script type="text/javascript" src="../../js/copyToClipboard.js"></script>
		<script type="text/javascript" src="../../js/jquery-2.2.0.min.js"></script>
		<script>
		function goBack() {
			window.history.back();
		}
		</script>';
        echo "I'm sorry ";
		echo ''. $_POST['first_name'] .'';
		echo ", I'm afraid i can't do that.<br><br>
		Er is iets fout gegaan, kijk het volgende na.<br /><br />";
        echo $error."<br /><br />";
        echo "Probeer het opnieuw.<br /><br />";
		echo 'Uw bericht: <p id="Comments">'. $_POST['comments'] .'</p>
		<button onclick="copyToClipboard(\'#Comments\')">kopieer bericht</button>
		<button onclick="goBack()">Ga terug</button>';
        die();
    }
 
 
    // validation expected data exists
    if(!isset($_POST['first_name']) ||
        !isset($_POST['last_name']) ||
        !isset($_POST['email']) ||
        !isset($_POST['telephone']) ||
		!isset($_POST['subject']) ||
        !isset($_POST['comments'])) {
        died('We are sorry, but there appears to be a problem with the form you submitted.');       
    }
 
     
 
    $first_name = $_POST['first_name']; // required
    $last_name = $_POST['last_name']; // required
    $email_from = $_POST['email']; // required
    $telephone = $_POST['telephone']; // not required
	$subject = $_POST['subject']; // required
    $comments = $_POST['comments']; // required
 
    $error_message = "";
    $email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
 
  if(!preg_match($email_exp,$email_from)) {
    $error_message .= '<b>Incorrect mail adres.</b> of niet ingevuld.<br />';
  }
 
    $string_exp = "/^[A-Za-z .'-]+$/";
 
  if(!preg_match($string_exp,$first_name)) {
    $error_message .= '<b>Incorrecte voornaam.</b> of niet ingevuld.<br />';
  }
 
  if(!preg_match($string_exp,$last_name)) {
    $error_message .= '<b>Incorrecte achternaam.</b> of niet ingevuld.<br />';
  }
 
 if(strlen($subject) < 6) {
    $error_message .= '<b>Onderwerp te kort.</b> of niet ingevuld.<br />';
  }
 
  if(strlen($comments) < 6) {
    $error_message .= '<b>Bericht te kort.</b> of niet ingevuld.<br>';
  }
 
  if(strlen($error_message) > 0) {
    died($error_message);
  }
 
	function clean_string($string) {
      $bad = array("content-type","bcc:","to:","cc:","href");
      return str_replace($bad,"",$string);
    }
	
    $email_message = "<html><body>";
	$email_message .= "Form details below.<br><br>";
 
    $email_message .= "First Name: ".clean_string($first_name)."<br>";
    $email_message .= "Last Name: ".clean_string($last_name)."<br>";
    $email_message .= "Email: ".clean_string($email_from)."<br>";
    $email_message .= "Telephone: ".clean_string($telephone)."<br>";
    $email_message .= "Comments: <br>".clean_string($comments)."<br>";
	$email_message .= "</body></html>";
 
	//mail copy for sender
	$email_message_copy = "<html><body>";
	$email_subject_copy .= 'KOPIE VAN UW BERICHT: ' . $email_subject . '';
	$email_message_copy = "(KOPIE) Form details below.<br><br>";
	 
    $email_message_copy .= "First Name: ".clean_string($first_name)."<br>";
    $email_message_copy .= "Last Name: ".clean_string($last_name)."<br>";
    $email_message_copy .= "Email: ".clean_string($email_from)."<br>";
    $email_message_copy .= "Telephone: ".clean_string($telephone)."<br>";
    $email_message_copy .= "Comments: <br>".clean_string($comments)."<br>";
	$email_message_copy .= "</body></html>";
	
// email headers
$headers = 'From: '.$email_from."\r\n".
'Reply-To: '.$email_from."\r\n" .
'Content-Type: text/html; charset=ISO-8859-1\r\n' .
'X-Mailer: PHP/' . phpversion();
@mail($email_to, $email_subject, $email_message, $headers); 
@mail($email_from, $email_subject_copy, $email_message_copy, $headers);
?>
 
<!-- success html -->
 
Bedankt voor het contact opnemen, we zullen zo snel mogelijk reageren.<br>
<B> HOU OOK UW SPAMFOLDER IN DE GATEN </B>
 
<?php
 
}
?>