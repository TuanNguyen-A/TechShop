<?php
session_start();
header('Access-Control-Allow-Origin: *');

require ('../db/dbhelper.php');
require ('../utils/utility.php');

$action = getPOST('action');

switch($action){
    case 'login':
        doLogin();
        break;
    case 'logout':
        doLogout();
        break;    
    case 'register':
        doRegister();
        break;
    case 'list':
        doUserList();
        break;
}

function doLogin(){
    $email = getPOST('email');
    $password = getPOST('password');

    $password = getPwdSecurity($password);

    $sql = "select * from users where email='$email' and password='$password'";

    $user = executeResult($sql, true);
    
    if($user != null){
        $email = $user['email'];
        $id = $user['id'];

        $token = getPwdSecurity($email.time().$id);

        setcookie('token', $token, time()+7*24*60*60, '/');

        $sql = "insert into login_token (id_user, token) values ('$id','$token')";
        execute($sql);

        $res = [
            "status" => 1,
            "msg" => "Login success!!!"
        ];
    }else{
        $res = [
            "status" => -1,
            "msg" => "Login fail!!!"
        ];
    }
    echo json_encode($res);
}

function doLogout(){
    $token = getCOOKIE('token');
    if(empty($token)){
        $res = [
            "status" => 1,
            "msg" => "Logout success!!!"
        ];
        echo json_encode($res);
        return;
    }

    $sql = "delete from login_token where token = '$token'";
    execute($sql);

    setcookie('token','',time()-7*24*60*60, '/');

    $res = [
        "status" => 1,
        "msg" => "Logout success!!!"
    ];
    echo json_encode($res);
    session_destroy();
    return;
}

function doRegister(){
    $username = getPOST('username');
    $fullname = getPOST('fullname');
    $email = getPOST('email');
    $password = getPOST('password');
    $address = getPOST('address');
    $phonenumber = getPOST('phonenumber');

    $sql = "select * from users where username = '$username' or email = '$email'";
    $result = executeResult($sql);
    if($result == null || count($result) == 0){
        $password = getPwdSecurity($password);
        $sql = "insert into users(fullname, username, email, password, address, phonenumber) values ('$fullname', '$username', '$email', '$password', '$address', '$phonenumber')";
        execute($sql);

        $res = [
            "status" => 1,
            "msg" => "Create account success!!!"
        ];
    }else{
        $res = [
            "status" => -1,
            "msg" => "Email|Username existed!!!"
        ];
    }
    echo json_encode($res);
}
