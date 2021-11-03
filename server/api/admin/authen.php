<?php
session_start();
header('Access-Control-Allow-Origin: *');

require ('../../db/dbhelper.php');
require ('../../utils/utility.php');

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

    $sql = "select * from admin where email='$email' and password='$password' and deleted=0";

    $user = executeResult($sql, true);
    
    if($user != null){
        $email = $user['email'];
        $id = $user['id'];

        $token = getPwdSecurity($email.time().$id);

        setcookie('token', $token, time()+7*24*60*60, '/');

        $sql = "insert into admin_token (id_admin, token) values ('$id','$token')";
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

    $sql = "delete from admin_token where token = '$token'";
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
    $fullname = getPOST('fullname');
    $email = getPOST('email');
    $password = getPOST('password');
    $address = getPOST('address');
    $phone_number = getPOST('phone_number');
    $deleted = 0;
    $created_at = $updated_at = date('Y-m-d H:s:i');

    $sql = "select * from admin where email = '$email' and deleted = 0";
    $result = executeResult($sql);
    if($result == null || count($result) == 0){
        $password = getPwdSecurity($password);
        $sql = "insert into admin(fullname, email, password, address, phone_number, created_at, updated_at, deleted) values ('$fullname', '$email', '$password', '$address', '$phone_number', '$created_at', '$updated_at', '$deleted')";
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

function doUserList(){
    $user = authenAdminToken();

    if($user == null){
        $res = [
            "status" => -1,
            "msg" => "Not login!!!",
            "userList" => []
        ];
        echo json_encode($res);
        return;
    }
    $search='';
    $addition='';
    $search = getPOST('search');

    if($search!=''){
        $addition = 'and (email like "%'.$search.'%" or fullname like "%'.$search.'%" or address like "%'.$search.'%" or phone_number like "%'.$search.'%")';
    }

    $sql = "select * from users where deleted=0 ".$addition;
    $result = executeResult($sql);
    $res = [
        "status" => 1,
        "msg" => "success!!!",
        "userList" => $result
    ];
    echo json_encode($res);
}