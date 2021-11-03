<?php
session_start();
header('Access-Control-Allow-Origin: *');

require ('../db/dbhelper.php');
require ('../utils/utility.php');

$action = getPOST('action');

switch($action){
    case 'list':
        doCategoryList();
        break;
    case 'add':
        doAdd();
        break;
    case 'update':
        doUpdate();
        break;
    case 'delete':
        doDelete();
        break;  
}

function doCategoryList(){
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
        $addition = 'and (name like "%'.$search.'%")';
    }

    $sql = "select * from category where 1=1 ".$addition." order by name asc";
    $result = executeResult($sql);
    $res = [
        "status" => 1,
        "msg" => "success!!!",
        "categoryList" => $result
    ];
    echo json_encode($res);
}

function doAdd(){
    $name = getPOST('name');

    $sql = "select * from category where name = '$name'";
    $result = executeResult($sql);
    if($result == null || count($result) == 0){
        $sql = "insert into category(name) values ('$name')";
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
    $name = getPOST('name');
    $sql = "select * from category where id = '$id'";
    $result = executeResult($sql);
    if(count($result) > 0){
        $sql = "update category set name = '$name' where id = ".$id;
        execute($sql);

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

function doDelete(){
    $id = getPOST('id');

    $sql = "select * from category where id = '$id'";
    $result = executeResult($sql);
    if(count($result) > 0){
        $sql = "delete from category where id = ".$id;
        execute($sql);

        $res = [
            "status" => 1,
            "msg" => "Delete success!!!"
        ];
    }else{
        
        $res = [
            "status" => -1,
            "msg" => "category doesn't exist!!!"
        ];
    }
    echo json_encode($res);
}