<?php 
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS,PUT, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Content-Type: application/json');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
// include_once 'config/core.php';
// require '../vendor/Firebase/php-jwt/src/BeforeValidException.php';
// require '../vendor/Firebase/php-jwt/src/ExpiredException.php';
// require '../vendor/Firebase/php-jwt/src/SignatureInvalidException.php';
// require '../vendor/Firebase/src/JWT.php';
use \Firebase\JWT\JWT;

require 'controller.php';
require '../vendor/autoload.php';   

$app = new \Slim\App;

/**
 * A middleware function called to the respective endpoints to 
 * validate the key
 */
$mw = function ($request, $response, $next) {

    $data = json_decode(file_get_contents("php://input"));     
    // get jwt
    $jwt=isset($data->jwt) ? $data->jwt : "";
    // return $response->withJson(array("message" =>$jwt));
    // exit();
    if($jwt){
        try {
            // decode jwt
            $decoded = JWT::decode($jwt,'mysecretkey', array('HS256'));
            $response = $next($request, $response);
            // set response code
            http_response_code(200);
            // show user details
            return $response->withJson(array(
                "message" => "Access granted.",
                "data" => $decoded->data
            ));
        }catch(Exception $e){
            // tell the user access denied
            return $response->withJson(array("message" => "Unable to parse message"),401);
        }
    }else{
        // tell the user access denied
        return $response->withJson(array("message" => "Access denied."),401);
    }
};
/**
 * the below endpoint creates the new user. 
 * Returns success 1 on successfully creating the user
 * Returns success 0 in case email already registered
 */
// $app->post('/users',Controller::class);
// ProfileController::class . ':unfollow'
/**
 * The below endpoint retrieves the user for given id. 
 * returns success 1 on retrieval
 * returns success 0 on failure
 */
///{id}
function generate_token($request,$response,$args){
    $password=md5($request->getParam('pwd'));
    $id = $args['id'];
    // $id= $request->getAttribute('routeInfo')[2]['id'];
    $link = mysqli_connect("localhost", "raj", "Raj@199704", "couponusers");
    $checkuser="SELECT * FROM users WHERE ID = '$id'";
    $userexist=mysqli_query($link, $checkuser);
    $checkresult=mysqli_num_rows($userexist);
    $sql= "SELECT * FROM users WHERE ID = '$id' and pwd='$password'";
    $result = mysqli_query($link, $sql);
    $numrows = mysqli_num_rows($result);
    if($numrows>0){
        while($row = mysqli_fetch_array($result)){
            $fname=$row[0];
            $lname=$row[1];
            $useremail=$row[2];
            $usercontact=$row[3];
        }
        $token = array(
            // "iss" => $iss,
            // "aud" => $aud,
            // "iat" => $iat,
            // "nbf" => $nbf,
            "data" => array(
                "id" => $id,
                "firstname" => $fname,
                "lastname" => $lname,
                "email" => $useremail,
                "contact"=>$usercontact
            )
         );
         http_response_code(200);
         $key='mysecretkey';
         // generate jwt
         $jwt = JWT::encode($token, $key);
         return $response->withJson(
                 array(
                    "success" => 1,
                     "message" => "Successful login.",
                     "jwt" => $jwt,
                     "id"=>$id,
                     "firstname" => $fname,
                     "lastname" => $lname,
                     "email" => $useremail,
                     "contact"=>$usercontact
                 )
         );
    }else{
        // tell the user login failed
        return $response->withJson(array("success" => 0,"errormessage"=>"Invalid Credentials","status code"=>200),200);
    }
}
$app->post('/tokengenerate/{id}',function(Request $request, Response $response, array $args)use($app){
    
    $jsonvalue= generate_token($request,$response,$args);//token generated

    return $jsonvalue;
});
$app->post('/users',function(Request $request, Response $response, array $args){
    

})->add($mw);

/**
 * Endpoint deletes the user for given id.
 * Returns HTTP response status 204 on success with empty body
 * Returns success 0 if user doesn't exist.
 */
// $app->delete('/users/{id}',function(Request $request, Response $response, array $args){

//     $userId = $args['id'];
//     $link = mysqli_connect("localhost", "raj", "Raj@199704", "couponusers");

//     $sql= "DELETE FROM users WHERE ID ='$userId'";
//     $result = mysqli_query($link,$sql);
//     if(mysqli_affected_rows($link)){
//         return $response->withJson(array(),204);
//     }
//     return $response->withJson(array('success' => 0,'errormessage'=>'user doesn\'t exist'),200);
//     // }
    
// });

$app->delete('/users/{id}',function(Request $request, Response $response, array $args){

        $userId = $args['id'];
        $link = mysqli_connect("localhost", "raj", "Raj@199704", "couponusers");
    
        $sql= "DELETE FROM users WHERE ID ='$userId'";
        $result = mysqli_query($link,$sql);
        if(mysqli_affected_rows($link)){
            return $response->withJson(array(),204);
        }
        return $response->withJson(array('success' => 0,'errormessage'=>'user doesn\'t exist'),200);

    });
/**
 * The endpoint updates details for the specific user.
 *  Returns success 1 on successfuly updated
 *  Returns success 0 on failure
 */
$app->put('/users/{id}',function(Request $request, Response $response,array $args)  use($app) {

    $userId = $args['id'];
    $link = mysqli_connect("localhost", "raj", "Raj@199704", "couponusers");
  
    //keeping here getparsed body() throwing warning
    // $parsedBody = $request->getParsedBody();
    // $fname = $parsedBody['fname'];
    // $lname = $parsedBody['lname'];
    // $phone = $parsedBody['phone'];
    // $email = $parsedBody['email'];

    $fname = $request->getParam('fname');
    $lname = $request->getParam('lname');
    $phone = $request->getParam('phone');
    $email = $request->getParam('email');
    $checkuser="SELECT * FROM users WHERE ID = '$userId'";
    $userexist=mysqli_query($link, $checkuser);
    $checkresult=mysqli_num_rows($userexist);
    // print_r($checkresult);
    // exit();
    if($checkresult){
        $sql= "UPDATE users set first_name='$fname',last_name='$lname',contact_no='$phone',email_id='$email' WHERE ID = '$userId'";
        mysqli_query($link,$sql);
        return $response->withJson(array("success" => 1,"message"=>"succesfully updated"),200);
    }
    return $response->withJson(array("success" => 0,'message'=>'User Doesn\'t Exist'),400);
    
});

$app->run();