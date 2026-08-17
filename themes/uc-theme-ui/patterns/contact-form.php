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
// Initialize variables
$form_submitted = false;
$form_success = false;
$total_errors = array();

// Only process form if it was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ucfirstname'])) {
    $form_submitted = true;
    
    // Declare the wp_mail parameters
    $to = 'information@untouchedcocktails.com'; 
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $subject = 'New Contact From: ';
    
    // Declare the Data vars
    $ucfirst = htmlspecialchars($_POST['ucfirstname']);
    $uclast = htmlspecialchars($_POST['uclastname']);
    $ucreturn_contact = htmlspecialchars($_POST['return-contact']);
    $enquiry_text = htmlspecialchars($_POST['enquiry']);

    // Validate the form
    $regex_chars_allowed_in_name = "/^[a-zA-ZäöüÄÖÜ ]*$/";
    $regex_phone_format = "/^\(?(\d{3})\)?[\s.-]?(\d{3})[\s.-]?(\d{4})$/";
    
    // Check if name only contains letters and whitespace
    if (!preg_match($regex_chars_allowed_in_name, $ucfirst)) {
        $total_errors[] = "Double check First Name.";
    } 
    // Check if name only contains letters and whitespace
    if (!preg_match($regex_chars_allowed_in_name, $uclast)) {
        $total_errors[] = "Double check Last Name.";
    } 

    // Check if e-mail address is well-formed
    if (!filter_var($ucreturn_contact, FILTER_VALIDATE_EMAIL)) {
        // If return-contact is not an email, check whether it's a phone number
        if (!preg_match($regex_phone_format, $_POST['return-contact'])) {
            $total_errors[] = "Please provide a valid email or phone number.";
        }
    }

    // If no errors, send the email
    if (empty($total_errors)) {
        // Add contact's name to email subject line
        $subject .= $ucfirst; 

        // Generate body of the email
        $body = "Name: " . $uclast . ", " . $ucfirst . "\n\n";
        $body .= "Contact: " . $ucreturn_contact . "\n\n";
        $body .= "Message:\n" . $enquiry_text;

        // Send the email
        $mail_sent = wp_mail($to, $subject, $body, $headers);
        
        if ($mail_sent) {
            $form_success = true;
        } else {
            $total_errors[] = "There was a problem sending your message. Please try again later.";
        }
    }
}
?>

<?php if ($form_submitted && $form_success): ?>
    <div class="form-success-message" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <p><strong>Thank you!</strong> Your message has been sent successfully. We'll get back to you soon.</p>
    </div>
<?php endif; ?>

<?php if ($form_submitted && !empty($total_errors)): ?>
    <div class="form-errors" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <?php foreach ($total_errors as $error): ?>
            <p id="error"><?php echo $error; ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form id="contact-form" method="POST" action="">

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
					<!-- fieldset>
						<legend>Make this post public?</legend>
						<br></br>
						<label for="blogY">Yes</label>
						<input type="radio" id = "blogY" name = "publish" required/>
						<label for="blogN">No</label>
						<input type="radio" id = "blogN" name = "publish" checked/>
					</fieldset>

					<br></br -->
					<label id="return" for="return-contact"> How can we reach you? (Not Public)</label> 
					<br></br>
					<input type = "text" placeholder="email, phone, etc..." id = "return-contact" name="return-contact" 
						onfocus="this.placeholder=''" 
						onblur="this.placeholder='Return Contact Info'" />
					<br></br>
					
			</fieldset>

			
  
		

		<button name="subBtn" type="submit" class="std-button" form="contact-form">
		Send Contact </button>

        

	</form>
