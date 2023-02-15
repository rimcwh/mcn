<?php

  //create short variable names
  $name = $_POST['name'] ;
  $email = $_POST['email'] ;
  $feedback = $_POST['feedback'] ;

  //set up some static information
  $toaddress = "testing@gmail.com" ;

  $subject = "Feedback from web site";

  $mailcontent = "Customer name: ".filter_var($name)."\n".
                 "Customer email: ".$email."\n".
                 "Customer comments:\n".$feedback."\n";

  //$fromaddress  = "From: nu@mcnsite.ddns.net\r\n";
  //$fromaddress  = "From: nu@114.32.71.101\r\n";
  $fromaddress  = "From: nu@114-32-71-101.hinet-ip.hinet.net\r\n";
  //$fromaddress .= "Bcc: testingabc@gmail.com";

  //invoke mail() function to send mail
  //mail($toaddress, $subject, $mailcontent, $fromaddress);

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Bob's Auto Parts - Feedback Submitted</title>
  </head>
  <body>

    <h1>Feedback submitted</h1>
    <p>Your feedback has been sent.</p>

  </body>
</html>
