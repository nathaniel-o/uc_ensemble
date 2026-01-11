<?php
/**
 * Title: contact-form
 * Slug: contact-form
 * Categories: forms
 * Description: Legacy contact form with PHP processing
 */
?>	

<?php
// At top of contact-form.php, before any HTML
/* wp_enqueue_style(
    'uc-contact-form',
    get_theme_file_uri('styles/contact-form.css'),
    array(),
    filemtime(get_theme_file_path('styles/contact-form.css'))
); */
?>

<?php
#  <?php echo get_theme_file_uri('form-handler.php')  ?
 #  "http://untouchedcocktails.com/wordpress/contact-us/"> 

#action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); 
     
$body = '';

#$whereDo = htmlspecialchars($_SERVER["PHP_SELF"]); 




  // Only process form if it was submitted
  if (isset($_POST) && !empty($_POST)) {
	
	#Declare the wp_mail parameters
		$to = 'information@untouchedcocktails.com'; 
		$headers = array('Content-Type: text/html; charset=UTF-8');  #  for CC, BCC ; not in use
		$body = '';
		$subject = 'New Contact From: ';
	#Declare the Error vars
		$first_err = $last_err = $enquiry_err = $contact_err = $blog_err = ' ' ;
		$total_errors = array(); 
	#Declare the Data vars
		$ucfirst = htmlspecialchars($_POST['ucfirstname']);
		$uclast = htmlspecialchars($_POST['uclastname']);
		$ucreturn_contact = htmlspecialchars($_POST['return-contact']);
		$body = htmlspecialchars($_POST['enquiry']);

	#Validate the form
		$regex_chars_allowed_in_name = " /^[a-zA-ZäöüÄÖÜ  ]*$/ " ; 
		$regex_phone_format = " /^\(?(\d{3})\)?[\s.-]?(\d{3})[\s.-]?(\d{4})$/ ";
		// check if name only contains letters and whitespace
		if (!preg_match($regex_chars_allowed_in_name, $ucfirst)) {
			$total_errors[] = "Double check First Name.";
		} 
		// check if name only contains letters and whitespace
		if (!preg_match($regex_chars_allowed_in_name, $uclast)) {
			$total_errors[] = "Double check Last Name.";
		} 

		// check if e-mail address is well-formed
		if (!filter_var($ucreturn_contact, FILTER_VALIDATE_EMAIL)) {
			$total_errors[] = "Invalid email format";

	
			#  IF return-contact is not an email, Check whether phone number
			if(!preg_match($regex_phone_format, $_POST['return-contact'])){

				$total_errors[] = "Is your contact information accurate?" ;


			}
		}

		#echo '<script> console.log( "SUM ERRORS" );</script>';
		#	echo '<script> console.log( " ' . gettype($total_errors) . ' " );</script>';


			// Log Each Error
			for($i=0; $i < count($total_errors) ; $i++){

				#echo '<script> console.log( "inner for"  );</script>';

				echo '<p id = "error"> '.  $total_errors[$i] .'</p>';
			
			}

		


		#else {  #  IF there are not errors

		    // Add contact's name to email subject line
			$subject .= $ucfirst; 

			//Generate body of the email
			$body .=    "          Name: " . $uclast . ", " . $ucfirst . "    Contact:" . $ucreturn_contact;

			$body_ = str_replace("          ", "\n\n", $body);
			




			

			
		


		#}
		
		
}  #  END ELSE (where ELSE = IF POSTED)



echo ' <script>
	console.log( "end of test" ); </script> ' ; 


wp_mail( $to, $subject, $body, $headers );

echo '<script> console.log( " ' . $body . ' " );</script>';

//sonco


?>



<form id="contact-form" > 

		<h4> Contact Us </h4>
		<fieldset class = "box squeezed">

			
				
				<!--p id="req">* = required</p-->
			
				
			
					
					<input type = "text" placeholder="First Name" id = "ucfirstname" name="ucfirstname" required 
						onfocus="this.placeholder=''" 
						onblur="this.placeholder='First Name'" />
					
						
						
					<input type = "text" placeholder="Last Name or Company" id = "uclastname" name="uclastname" 
						onfocus="this.placeholder=''" 
						onblur="this.placeholder='Last Name or Company'" /><br></br>
					<br></br> 
				
					<textarea id="enquiry" name="enquiry" rows="10" cols="30" placeholder="How can we help you?*"></textarea>

					<br></br>
					<fieldset>
						<legend>Make this post public?</legend>
						<br></br>
						<label for="blogY">Yes</label>
						<input type="radio" id = "blogY" name = "publish" required/>
						<label for="blogN">No</label>
						<input type="radio" id = "blogN" name = "publish" checked/>
					</fieldset>

					<br></br>
					<label id="return" for="return-contact"> How can we reach you? (Not Public)</label> 
					<br></br>
					<input type = "text" placeholder="email, phone, etc..." id = "return-contact" name="return-contact" 
						onfocus="this.placeholder=''" 
						onblur="this.placeholder='Return Contact Info'" />
					<br></br>
					
			</fieldset>

			
  
		

		<button name = "subBtn" type="submit" class="std-button" form="contact-form"
		formmethod ="POST" action = "" >
		Send Contact </button>

        

	</form>


<script> 
	/* document.addEventListener("load", doAModal());

		function doAModal(){
			let show = document.querySelector(".std-button");
			console.log(show);
			let dialog = document.querySelector("dialog");
			console.log(dialog);
			console.log("FROM CONTACT");

			show.addEventListener("click", () => {

			dialog.showModal();

			let close = document.querySelector("closeBtn");

				close.addEventListener("click", () => {

					

					dialog.close();

				});

			});
			
		}; */

</script>

