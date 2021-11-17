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
    }
    
    $fullname = getPOST('fullname');
    $phone_number = getPOST('phone_number');
    $email = getPOST('email');
    $address = getPOST('address');
    $note = getPOST('note');
    $cart = getPOST('cart');
    $order_date = date('Y-m-d H:s:i');

    $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
    mysqli_set_charset($conn, 'UTF-8');

    $sql = "INSERT INTO orders(fullname, email, address, phone_number, note, order_date, status) 
            VALUES ('$fullname', '$email', '$address', '$phone_number', '$note', '$order_date', 0)";
    
    if (mysqli_query($conn, $sql)) {
        $last_id = mysqli_insert_id($conn);
        $total = 0;

        for($i = 0; $i < count($cart); $i++){
            $item = $cart[$i];
            $product_id = $item['id'];
            $num = $item['quantity'];

            //giá thời điểm mua
            $sql = "SELECT price, discount 
                    FROM products
                    WHERE id = $product_id";
            $resultset = mysqli_query($conn, $sql);
            $data = mysqli_fetch_array($resultset, 1);
            $price = $data['price'] - $data['price']*($data['discount']/100);
            $total += $price; 

            $sql = "INSERT INTO order_details(order_id, product_id, price, num)
                    VALUES('$last_id', '$product_id', '$price', '$num')";
            mysqli_query($conn, $sql);
        }

        $sql = "UPDATE orders SET total_money = '$total' WHERE id = '$last_id'";
        mysqli_query($conn, $sql);

        $res = [
            "status" => 1,
            "msg" => "Order success!!!"
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