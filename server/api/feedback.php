<?php
session_start();
header('Access-Control-Allow-Origin: *');

require ('../db/dbhelper.php');
require ('../utils/utility.php');


$action = getPOST('action');

switch($action){
    case 'list':
        doFeedbackList();
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
}

function doFeedbackList(){
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

    $sql = "select * from feedback where 1=1 ".$addition." order by readed asc";
    $result = executeResult($sql);
    $res = [
        "status" => 1,
        "msg" => "success!!!",
        "feedbackList" => $result
    ];
    echo json_encode($res);
    return;
}

function doUpdate(){
    $id = getPOST('id');
    $sql = "update feedback set readed = 1 where id = ".$id;
    execute($sql);
    $res = [
        "status" => 1,
        "msg" => "success!!!"
    ];
    echo json_encode($res);
    return;
}

function doQuery(){
    $id = getPOST('id');
    $sql = "select * from feedback where id = ".$id;
    $feedback = executeResult($sql,true);
    if($feedback){
        $res = [
            "status" => 1,
            "msg" => "success!!!",
            "feedback" => $feedback
        ];
    }else{
        $res = [
            "status" => -1,
            "msg" => "fail!!!"
        ];
    }
    echo json_encode($res);
    return;
}

function doDelete(){
    $id = getPOST('id');
    $sql = "delete from feedback where id = ".$id;
    execute($sql);
    $res = [
        "status" => 1,
        "msg" => "success!!!",
    ];
    echo json_encode($res);
    return;
}