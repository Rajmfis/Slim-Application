<?php
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

require 'dbconfig.php';

final class Controller
{
    public function createUser(Request $request, Response $response): ResponseInterface
    {
        $fname = $request->getParam('fname');
        $lname = $request->getParam('lname');
        $phone = $request->getParam('phone');
        $email = $request->getParam('email');
    
        // $parsedBody = $request->getParsedBody();
        // $fname = $parsedBody['fname'];
        // $lname = $parsedBody['lname'];
        // $phone = $parsedBody['phone'];
        // $email = $parsedBody['email'];
    
        $password=md5($request->getParam('pwd'));
        $link = connect_db();
        $checkuser="SELECT * FROM users WHERE EMAIL_ID = '$email'";
        $userexist=mysqli_query($link, $checkuser);
        $checkresult=mysqli_num_rows($userexist);
        if($checkresult){
            return $response->withJson(array("success" => 0,"errormessage"=>"Email_Id already exists","error code"=>200),200);
        }
    
        $sql = "INSERT INTO users(first_name,last_name,email_id,contact_no,pwd) VALUES ('$fname','$lname','$email','$phone','$password');";
        
        if(mysqli_query($link, $sql)){
            $id_Fetch="SELECT ID FROM USERS WHERE EMAIL_ID='$email'";
            $row=mysqli_fetch_array(mysqli_query($link, $id_Fetch));
            return $response->withJson(array("success" => 1, "emailId" => $email ,"fname"=>$fname,"lname"=>$lname,"contactno"=>$phone,"ID"=>$row['ID']),201);
        }
    }

}

?>