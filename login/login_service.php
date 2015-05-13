<?php
/**
Project: ynotfood.com webservices
Functionality: Web service to process login.
Author: Vishnu Priyan
Create Date: 24-Dec-2014 kk
*/

//code to display errors. To be commented out before migrating to UAT Environment.
ini_set('display_errors',true);
error_reporting(E_ALL);

//import the nusoap library. Always use require_once to avoid warnings.
require_once('../lib/nusoap.php');

//create an object for the nusoap server.
$server=new nusoap_server();

//configure nusoap WSDL. Give a name relevant to the functionality.
$server->configureWSDL("login","urn:login");

//register the method used to process the request along with inputs and outputs
$server->register(
        "login_user",  //method to be processed.
        array("username"=>'xsd:string',"password"=>"xsd:string","token"=>"xsd:string"), //inputs in array
        array("username"=>"xsd:string","email"=>"xsd:string","user_role"=>"xsd:string","user_role"=>"xsd:string","name"=>"xsd:string","last_login_at"=>"xsd:string","user_id"=>"xsd:inter") //outputs in array
        );
        
//process nusoap request and provide response. To be included in all codes        
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

/**
Function: login_user
Parameters: username,password,token
Return Type: array()
Return data: array(username,email,user_role,name,last_login_at,user_id)
*/
        
function login_user($username,$password,$token)
{
	//Initializing the GLOBAL variable for Database connection
    GLOBAL $conn;
    
    //validate auth key. Return value: 1-Valid auth key, 0- Invalid auth key
    $authResult = authToken($token);
    
    //Check login details exists in the DB and return the value
    if($authResult==1){
    $getLoginQuery = "select username,email,user_role,name,last_login_at,user_id from admin_login where (username='$username' or email='$username') and password='$password'";
    $getLogin = mysqli_query($conn,$getLoginQuery);
    $loginStatus = mysqli_num_rows($getLogin);
        if($loginStatus == 1){
                $loginDetails = $getLogin -> fetch_row();
                $userDetails = array(
                                "username"=>$loginDetails[0],
                                "email"=>$loginDetails[1],
                                "user_role"=>$loginDetails[2],
                                "name"=>$loginDetails[3],
                                "last_login_at"=>$loginDetails[4],
                                "user_id"=>$loginDetails[5]
                                );
                mysqli_query($conn,"update admin_login set last_login_at=current_timestamp where user_id='".$loginDetails[5]."'");
        }
    }
    return $userDetails;
}

?>