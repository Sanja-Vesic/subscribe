<?php
require './vendor/autoload.php';




/**********helper functions *****/
function clean($string){
    return htmlentities($string);
}
function redirect($location){

    return header("Location: {$location}");
}

function set_message($message){
if(!empty($message)){
    $_SESSION['message'] = $message;
} else{
    $message = "";
}

}

function display_message()
{
    if(isset($_SESSION['message'])){
        echo $_SESSION['message'];
            unset($_SESSION['message']);
    }
    
}



function token_generator(){
    md5(uniqid(mt_rand(), true));
    return $token;}
function validation_errors($error_message){
    $error_message = <<<DELIMITER
    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <h4>Warning!</h4>$error_message</div>
DELIMITER;
return $error_message;
}

function email_exist($email){
    $sql = "SELECT id FROM users WHERE email = '$email'";
    $result = query($sql);
      if(row_count($result) ==1) {
        return true;
    } else{
        return false;
    }


}




function send_email($email=null,$subject=null,$msg=null,$headers=null){

    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->SMTPDebug = 1; 
    // $mail = new PHPMailer();
    // try {
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      
    $mail->isSMTP();                                            
    $mail->Host       = Config::SMTP_HOST;             
    $mail->SMTPAuth   = true;                                  
    $mail->Username   = Config::SMTP_USER;                     
    $mail->Password   = Config::SMTP_PASSWORD;                              
    $mail->SMTPSecure = 'tls';        
    $mail->Port       = Config::SMTP_PORT;   
    
        $mail->setFrom('sanja.vesic@hotmail.rs', 'Sanja Vesic');
        $mail->addAddress($email);

    $mail->isHTML(true);                                 
    $mail->Subject = $subject;
    $mail->Body    = $msg;
    $mail->AltBody = $msg;
    if(!$mail->Send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
     } else {
        echo "Message has been sent";
     }
//     $mail->send();
//     echo 'Message has been sent';
// } catch (Exception $e) {
//     echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
// }

//    return mail($email,$subject,$msg,$headers);



}

function validate_user_registration(){
  
    $errors = [];
    if($_SERVER['REQUEST_METHOD'] == "POST"){
       $email        = clean($_POST['email']);
       if(email_exist($email)){          
        $errors[] =  "Sorry that email is already subscribed";
    }
   
       if(!empty($errors)){
           foreach($errors as $error){
            echo validation_errors($error);

           }
       } else{
           if (register_user($email)){
           
            set_message("<p class='bg-success text-center'>Please check your email or spam folder for activation link</p>");
            // redirect("index.php");
           }
           else{
            set_message("<p class='bg-success text-center'>Sorry you can not subscribe!</p>");
            // redirect("index.php");
           }
       }
          }
   
} 


/**************Register user functions****************/
function register_user($email){
$email = escape($email);


    if(email_exist($email)){
        return false;
    }  else { 
        getIp(); 
        $time = date('Y-m-d H:i:s');
        $time_now = strtotime($time);
        $validation_code = md5(microtime());
        $sql = "INSERT INTO users(email,validation_code, active,time_now)";
        $sql.= " VALUES('$email','$validation_code', 0,$time_now)";
        $result = query($sql);
        confirm($result);
        $subject= "Activate Account";
        $msg = "Please click the link below to activate your account
       <a href=\"http://localhost/subs/exercise-files/exercise-files/activate.php?email=$email&code=$validation_code&time_now=$time_now\">;
       LINK HERE </a>";
        $headers = "From: norepply@yourwebsite.com";
        send_email($email, $subject, $msg, $headers);
      
       

        return true;
    }  
       
} 


/**************Activate user functions****************/
function activate_user(){
if($_SERVER['REQUEST_METHOD'] == "GET"){
    if(isset($_GET['email'])){
       $sql = "SELECT time_now FROM users WHERE email='".escape($_GET['email'])."' AND validation_code = '".escape($_GET['code'])."' AND  time_now = '".escape($_GET['time_now'])."' ";
        $result = query($sql);
        confirm($result);
    
        $now = date('Y-m-d H:i:s');
        $time = strtotime($now);      
        
      
      while ($row = mysqli_fetch_array($result)) {
             
                foreach($row as $key => $value) {  
                   
                    if(row_count($result) == 1 && ($time - $value) <60) {   
                        $sql="INSERT INTO verified_users SELECT  email, dateNow FROM users WHERE email='".escape($_GET['email'])."'";

                            $result2 = query($sql);
                            confirm($result2);
                            $sql22 = "DELETE FROM users WHERE email='".escape($_GET['email'])."'";
                            $result22 = query($sql22);
                            confirm($result22);
                        set_message("<p class='bg-success'>You are now subscribed!</p>");
                        }
                                else if(($time - $value) >= 60){
                                    $sql = "DELETE FROM users WHERE email='".escape($_GET['email'])."'";
                                    $result3 = query($sql);
                                    confirm($result3);
                                                //  set_message("<p class='bg-success'>Sorry, your link expired</p>");                                               
                                                
                                                  redirect("subscribeAgain.php");
                                                              }                                   
                                        else{
                                            set_message("<p class='bg-success'>Sorry, your account could not be activated</p>");
                                        }               
               }       
            
    }   
    }}}

    function getIp(){
        $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

        try{
            //$ip=$_GET['ip'];
            $statusCode='';
            $ipAddress='';
            $countryCode='';
            $countryName='';
            $regionName='';
            $zipCode='';
            $latitude='';
            $longitude='';
            $timeZone='';
            $apikey='84aa78b7862a80ac88e809e53254fe72ae25566bd4b6ac676080f7d15a324ef9';
            $url = 'http://api.ipinfodb.com/v3/ip-city/?key='.$apikey.'&format=json&ip='.$ip;
            $response = file_get_contents($url);
             $json_array=json_decode($response,true);
             
             
               function insert_into_database($statusCode,$statusMessage,$ipAddress,$countryCode,$countryName,$regionName,$cityName,$zipCode,$latitude,$longitude){
                //    require_once('db_config.php');
                   if($statusCode=='OK'){
                       $sql='insert into users(statusCode,ipAddress,countryCode,countryName,regionName,zipCode,latitude,longitude,timeZone) value (?,?,?,?,?,?,?,?,?)';
                       $stm=mysqli_prepare($conn,$sql);
                       mysqli_stmt_bind_param($stm,"sssssssss",$statusCode,$ipAddress,$countryCode,$countryName,$regionName,$zipCode,$latitude,$longitude,$timeZone);
                       mysqli_stmt_execute($stm);
                   }
               } 
           
                
            function display_array_recursive($json_rec){
                   if($json_rec){
                       foreach($json_rec as $key=> $value){
                           if(is_array($value)){
                               display_array_recursive($value);
                           }else{
                               echo $key.'--'.$value.'<br>';
                               
                               if($key=='statusCode'){
                                   $statusCode=$value;
                               }
                               if($key=='statusMessage'){
                                   $statusMessage=$value;
                               }
                               if($key=='ipAddress'){
                                   $ipAddress=$value;
                               }
                               if($key=='countryCode'){
                                   $countryCode=$value;
                               }
                               if($key=='countryName'){
                                   $countryName=$value;
                               }
                               if($key=='regionName'){
                                   $regionName=$value;
                               }
                               if($key=='cityName'){
                                   $cityName=$value;
                               }
                               if($key=='zipCode'){
                                   $zipCode=$value;
                               }
                               if($key=='latitude'){
                                   $latitude=$value;
                               }
                               if($key=='longitude'){
                                   $longitude=$value;
                                   insert_into_database($statusCode,$statusMessage,$ipAddress,$countryCode,$countryName,$regionName,$cityName,$zipCode,$latitude,$longitude);
                               }
                           }	
                       }	
                   }	
               }
                 display_array_recursive($json_array);
           }catch(Exception $e){
               echo $e->getMessage();
           }
    }
    
?>
