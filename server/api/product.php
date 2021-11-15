<?php
session_start();
header('Access-Control-Allow-Origin: *');

require_once ('../db/dbhelper.php');
require_once ('../db/config.php');
require_once ('../utils/utility.php');

$action = getPOST('action');

switch($action){
    case 'list':
        doProductList();
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
    case 'categorize':
        doProductHomeList();
        break; 
}

function doProductHomeList(){
    $cateList = [];
    $sql = "select * from category";
    $cateList = executeResult($sql);
    $productList = [];
    
    for($i = 0; $i < count($cateList); $i++){
        $cate = $cateList[$i];
        $cate_id = $cate['id'];
        $result = [];
        $sql = "SELECT products.* FROM products
            LEFT JOIN product_category ON products.id = product_category.product_id
            LEFT JOIN category ON category.id = product_category.category_id
            WHERE products.deleted = 0 AND product_category.category_id = $cate_id
            ORDER BY products.title asc
        ";
        $result = executeResult($sql);
        if($result){
            $productList += [$cate_id => $result];
        }
    }

    $res = [
        "status" => 1,
        "msg" => "success!!!",
        "productList" => $productList,
        "categoryList" => $cateList
    ];
    echo json_encode($res);
    return;
}



function doProductList(){
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
    $category_id='';
    $search = getPOST('search');
    $category_id = getPOST('category_id');

    if($category_id!=''){
        $addition = $addition."and category_id = '$category_id'";
    }


    if($search!=''){
        $addition = $addition.'and (title like "%'.$search.'%")';
    }

    $sql = "SELECT products.*
    FROM products 
    LEFT JOIN product_category ON products.id = product_category.product_id
    LEFT JOIN category ON category.id = product_category.category_id
    WHERE products.deleted = 0 ".$addition."
    ORDER BY products.title asc
    ";
    $productList = executeResult($sql);

    

    $product_category = [];
    if($productList){
        for($i=0; $i < count($productList);$i++){
            $id = $productList[$i]['id'];
            $cateList = [];
            $sql = "SELECT category.name
                FROM products
                JOIN product_category ON products.id = product_category.product_id
                JOIN category ON category.id = product_category.category_id
                WHERE products.deleted = 0 ".$addition." and products.id = $id
                ORDER BY title asc
                ";
            $cateList = executeResult($sql);

            if($cateList){
                $product_category += [$id => $cateList];
            }
        }
    }
    $res = [
        "status" => 1,
        "msg" => "success!!!",
        "productList" => $productList,
        "product_category" => $product_category
    ];
    echo json_encode($res);
    return;
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

    $screen_size = getPOST('screen_size');
    $screen_technology = getPOST('screen_technology');
    $rear_camera = getPOST('rear_camera');
    $front_camera = getPOST('front_camera');
    $chipset = getPOST('chipset');
    $ram = getPOST('ram');
    $main_memory = getPOST('main_memory');
    $battery = getPOST('battery');
    $sim = getPOST('sim');
    $operating_system = getPOST('operating_system');

    $sql = "insert into products(admin_create_id, title, price, quantity, discount, thumbnail, description, created_at, updated_at, deleted) 
        values ( '$admin_create_id', '$title', '$price', '$quantity', '$discount', '$thumbnail', '$description', '$created_at', '$updated_at', '0')";
    
    
    if (mysqli_query($conn, $sql)) {
        $last_id = mysqli_insert_id($conn);
        
        //insert category
        for($i = 0; $i<count($category_id); $i++){
            $sql = "insert into product_category (product_id, category_id) values ($last_id ,$category_id[$i])";
            mysqli_query($conn, $sql);
        }

        //insert specification
        $sql = "insert into specification(id, screen_size, screen_technology, rear_camera, front_camera, chipset, ram, main_memory, battery, sim, operating_system) 
        values ('$last_id', '$screen_size', '$screen_technology', '$rear_camera', '$front_camera', '$chipset', '$ram', '$main_memory', '$battery', '$sim', '$operating_system')";
        mysqli_query($conn, $sql);
        
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
    $title = getPOST('title');
    $price = getPOST('price');
    $discount = getPOST('discount');
    $quantity = getPOST('quantity');
    $thumbnail = getPOST('thumbnail');
    $description = getPOST('description');
    $screen_size = getPOST('screen_size');
    $screen_technology = getPOST('screen_technology');
    $rear_camera = getPOST('rear_camera');
    $front_camera = getPOST('front_camera');
    $chipset = getPOST('chipset');
    $ram = getPOST('ram');
    $main_memory = getPOST('main_memory');
    $battery = getPOST('battery');
    $sim = getPOST('sim');
    $operating_system = getPOST('operating_system');

    

    if(getPOST('category_id')==""){
        $category_id = [];
    }else{
        $category_id = getPOST('category_id');
    }

    $sql = "select * from products where id = '$id'";
    $result = executeResult($sql);
    if(count($result) > 0){
        $sql = "update products set title = '$title', price = '$price', discount = '$discount', quantity = '$quantity', thumbnail = '$thumbnail', description = '$description' where id = ".$id;
        execute($sql);

        //update category
        $sql = "delete from product_category where product_id = '$id'";
        execute($sql);

        for($i = 0; $i<count($category_id); $i++){
            $sql = "insert into product_category (product_id, category_id) values ($id ,$category_id[$i])";
            execute($sql);
        }

        //update specification
    $sql = "update specification set screen_size = '$screen_size', screen_technology = '$screen_technology', rear_camera = '$rear_camera', 
        front_camera = '$front_camera', chipset = '$chipset', ram = '$ram', main_memory = '$main_memory', battery = '$battery', sim = '$sim', operating_system = '$operating_system'
        where id = ".$id;
    execute($sql);

        $res = [
            "status" => 1,
            "msg" => "Update success!!!",
            "cate" => $category_id
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
    $sql = "SELECT products.*, specification.screen_size, specification.screen_technology, specification.rear_camera, specification.front_camera, specification.chipset, specification.ram, specification.main_memory, specification.battery, specification.sim, specification.operating_system 
    FROM products 
    LEFT JOIN specification ON products.id = specification.id
    WHERE products.id = '$id' AND products.deleted = 0";

    $product = executeResult($sql, true);
    
    if($product==null){
        $res = [
            "status" => -1,
            "msg" => "Query fail!!!"
        ];
    }else{
        $product_id = $product['id'];

        //Category List
        $sql = "SELECT product_category.category_id
        FROM products
        LEFT JOIN product_category ON products.id = product_category.product_id
        LEFT JOIN category ON category.id = product_category.category_id
        WHERE products.deleted = 0 and products.id = $product_id
        ORDER BY title asc
        ";

        $result = executeResult($sql);

        if(!$result){
            $result = [];
        }
        $categoryList = [];
        
        for($i = 0; $i<count($result);$i++){
            $categoryList[$i] = $result[$i]['category_id'];
        }

        //Specification
        // $sql = "SELECT specification.*
        // FROM products
        // LEFT JOIN specification ON products.id = specification.id
        // WHERE products.deleted = 0 and products.id = $product_id
        // ORDER BY title asc
        // ";
        
        $res = [
            "status" => 1,
            "msg" => "Query success!!!",
            "product" => $product,
            "categoryList" => $categoryList
        ];
    }

    echo json_encode($res);
}