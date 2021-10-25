<?php
require_once('config.php');

//insert, update, delete
function execute($sql){
    $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
    mysqli_set_charset($conn, 'UTF-8');

    mysqli_query($conn, $sql);

    mysqli_close($conn);
}

//select
function executeResult($sql, $isSingleRecord = false){
    $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
    mysqli_set_charset($conn, 'UTF-8');
    $resultset = mysqli_query($conn, $sql);
    if(!$isSingleRecord){
        $data = [];
        while($row = mysqli_fetch_array($resultset, 1)){
            $data[] = $row;
        }
    }else{
        $data = mysqli_fetch_array($resultset, 1);
    }
    mysqli_close($conn);
    return $data;
}