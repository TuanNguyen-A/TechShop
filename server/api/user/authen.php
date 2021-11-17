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
    case 'update':
        doUpdate();
        break;
    case 'query':
        doQuery();
        break;   
    case 'delete':
        doDelete();
        break; 
    case 'info': 
        doInfo();
}

function doInfo(){
    $user = authenUserToken();
    if($user){
        $res = [
            "status" => 1,
            "msg" => "success!!!",
            "user" => $user
        ];
    }else{
        $res = [
            "status" => 1,
            "msg" => "not logged in!!!"
        ];
    }
    echo json_encode($res);
}

function doLogin(){
    $email = getPOST('email');
    $password = getPOST('password');

    $password = getPwdSecurity($password);

    $sql = "select * from users where email='$email' and password='$password' and deleted=0";

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
            "msg" => "Login fail!!!",
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

    $sql = "select * from users where email = '$email' and deleted = 0";
    $result = executeResult($sql);
    if($result == null || count($result) == 0){
        $password = getPwdSecurity($password);
        $sql = "insert into users(fullname, email, password, address, phone_number, created_at, updated_at, deleted) values ('$fullname', '$email', '$password', '$address', '$phone_number', '$created_at', '$updated_at', '$deleted')";
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

function doUpdate(){
    $id = getPOST('id');
    $fullname = getPOST('fullname');
    $password = getPOST('password');
    
    $address = getPOST('address');
    $phone_number = getPOST('phone_number');
    $updated_at = date('Y-m-d H:s:i');

    $sql = "select * from users where id = '$id' and deleted = 0";
    $result = executeResult($sql);
    if(count($result) > 0){
        if($password){
            $password = getPwdSecurity($password);
            $sql = "update users set fullname = '$fullname',password = '$password',address = '$address',phone_number = '$phone_number', updated_at = '$updated_at' where id = ".$id;
            execute($sql);
        }else{
            $sql = "update users 
                    set fullname = '$fullname',address = '$address',phone_number = '$phone_number', updated_at = '$updated_at' 
                    where id = ".$id;
            execute($sql);
        }

        unset($_SESSION['user']);

        $res = [
            "status" => 1,
            "msg" => "Update success!!!"
        ];
    }else{
        
        $res = [
            "status" => -1,
            "msg" => "Update fail!!!"
        ];
    }
    echo json_encode($res);
}

function doQuery(){
    $id = getPOST('id');
    $sql = "select * from users where id = '$id' and deleted = 0";
    $user = executeResult($sql, true);
    if($user==null){
        $res = [
            "status" => -1,
            "msg" => "Query fail!!!"
        ];
    }else{
        $res = [
            "status" => 1,
            "msg" => "Query success!!!",
            "user" => $user
        ];
    }

    echo json_encode($res);
}

function doDelete(){
    $id = getPOST('id');
    $updated_at = date('Y-m-d H:s:i');
    $sql = "select * from users where id = '$id' and deleted = 0";
    $result = executeResult($sql);
    if(count($result) > 0){
        $sql = "update users set updated_at = '$updated_at', deleted = '1' where id = '$id'";
        execute($sql);
        $t = $sql;
        $res = [
            "status" => 1,
            "msg" => "Update success!!!",
            "sql" => $t
        ];
    }else{
        
        $res = [
            "status" => -1,
            "msg" => "Update fail!!!"
        ];
    }
    echo json_encode($res);
}

