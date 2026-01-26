<?php
 	 #  "http://untouchedcocktails.com/wordpress/contact-us/"> 

	#action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); 
     

	$whereDo = htmlspecialchars($_SERVER["PHP_SELF"]); 
	#echo $whereDo;


	$to = 'contact@untouchedcocktails.com';
	$subject = 'The Test Subject';

    #Returns expected path, but still gives 404 page
	echo '<script> console.log( " ' . get_theme_file_uri('form-handler.php') . ' " );</script>';


	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		$body = "an improvement"; 

		echo '
		Welcome <?php echo $_POST["name"]; ?><br>
		Your email address is: <?php echo $_POST["email"]; ?> ';


	

	} else {
		$body = "Errors Ensued";	
		
		
	}

		echo ' <script>
		console.log( "end of test" ); </script> ' ; 

	$headers = array('Content-Type: text/html; charset=UTF-8');
	
	wp_mail( $to, $subject, $body, $headers );
    
	echo '<script> console.log( " ' . $body . ' " );</script>';

	
    ?>