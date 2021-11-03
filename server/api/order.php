<?php
session_start();
header('Access-Control-Allow-Origin: *');

require_once ('../db/dbhelper.php');
require_once ('../db/config.php');
require_once ('../utils/utility.php');

$action = getPOST('action');

switch($action){
    case 'list':
        doOrderList();
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
    case 'query':
        doQuery();
        break; 
}

function doOrderList(){
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

    $sql = "SELECT orders.*
    FROM orders 
    WHERE 1=1 ".$addition."
    ORDER BY order_date, status asc
    ";
    $orderList = executeResult($sql);
    
    // $product_category = [];
    // if($productList){
    //     for($i=0; $i < count($productList);$i++){
    //         $id = $productList[$i]['id'];
    //         $cateList = [];
    //         $sql = "SELECT category.name
    //             FROM products
    //             JOIN product_category ON products.id = product_category.product_id
    //             JOIN category ON category.id = product_category.category_id
    //             WHERE products.deleted = 0 ".$addition." and products.id = $id
    //             ORDER BY title asc
    //             ";
    //         $cateList = executeResult($sql);

    //         if($cateList){
    //             $product_category += [$id => $cateList];
    //         }
    //     }
    // }
    $res = [
        "status" => 1,
        "msg" => "success!!!",
        "orderList" => $orderList
    ];
    echo json_encode($res);
}

function doAdd(){
    $admin = authenAdminToken();
    if($admin == null){
        $res = [
            "status" => -1,
            "msg" => "Not login!!!"
        ];
        echo json_encode($res);
        return;
    }
    $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
    mysqli_set_charset($conn, 'UTF-8');

    if(getPOST('category_id')==""){
        $category_id = [];
    }else{
        $category_id = getPOST('category_id');
    }
    

    $admin_create_id=$admin['id'];
    $thumbnail = getPOST('thumbnail');
    $title = getPOST('title');
    $quantity = getPOST('quantity');
    $price = getPOST('price');
    $discount = getPOST('discount');
    $description = getPOST('description');
    $created_at = $updated_at =  date('Y-m-d H:s:i');

    $sql = "insert into products(admin_create_id, title, price, quantity, discount, thumbnail, description, created_at, updated_at, deleted) values ( '$admin_create_id', '$title', '$price', '$quantity', '$discount', '$thumbnail', '$description', '$created_at', '$updated_at', '0')";
    
    
    if (mysqli_query($conn, $sql)) {
        $last_id = mysqli_insert_id($conn);
        

        
        for($i = 0; $i<count($category_id); $i++){
            $sql = "insert into product_category (product_id, category_id) values ($last_id ,$category_id[$i])";
            mysqli_query($conn, $sql);
        }
        mysqli_close($conn);

        $res = [
            "status" => 1,
            "msg" => "Create account success!!!"
        ];
        echo json_encode($res);
        return;
    } else {
        mysqli_close($conn);
        $res = [
            "status" => -1,
            "msg" => "insert fail!!!"
        ];
        echo json_encode($res);
        return;
    }
    
}

function doUpdate(){
    $id = getPOST('id');

    $sql = "select * from orders where id = '$id'";
    $result = executeResult($sql);
    if(count($result) > 0){
        $sql = "update orders set status = 1 where id = ".$id;
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

    $sql = "select * from products where id = '$id' and deleted = 0";
    $result = executeResult($sql);
    if(count($result) > 0){
        $sql = "update products set deleted = 1 where id = ".$id;
        execute($sql);

        $res = [
            "status" => 1,
            "msg" => "Delete success!!!"
        ];
    }else{
        
        $res = [
            "status" => -1,
            "msg" => "product doesn't exist!!!"
        ];
    }
    echo json_encode($res);
}

function doQuery(){
    $id = getPOST('id');
    $sql = "select * from orders where id = '$id'";
    $order = executeResult($sql, true);
    
    if($order==null){
        
        $res = [
            "status" => -1,
            "msg" => "Query fail!!!"
        ];
    }else{
        $order_id = $order['id'];
        $sql = "select * from order_details where order_id = '$order_id'";
        $order_detail = executeResult($sql);
        
        $res = [
            "status" => 1,
            "msg" => "Query success!!!",
            "order" => $order,
            "order_detail" => $order_detail,

        ];
    }

    echo json_encode($res);
}