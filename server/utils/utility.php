<?php
function pagination($number, $page, $additional){
 //-------------
    if($number>1){
        echo '<ul class="pagination">';

        if($page > 1){
            echo'<li class="page-item"><a class="page-link" href="?page='.($page-1).$additional.'">Previous</a></li>';
        }

        $availablePages = [1,$page - 1, $page, $page +1,  $number];
        $isFirst = $isLast = false;
        for($i = 0;$i < $number;$i++){
            if(!in_array($i+1, $availablePages)){
                if(!$isFirst && $page > 3){
                    echo '<li class="page-item "><a class="page-link" href="?page='.($page-2).$additional.'">...</a></li>';
                    $isFirst = true;
                }
                if(!$isLast && $i > $page){
                    echo '<li class="page-item "><a class="page-link" href="?page='.($page+2).$additional.'">...</a></li>';
                    $isLast = true;
                }
                continue;
            }
            if($page == ($i+1)){
                echo '<li class="page-item active"><a class="page-link" href="?page='.($i+1).$additional.'">'.($i+1).'</a></li>';
            }else{
                echo '<li class="page-item"><a class="page-link" href="?page='.($i+1).$additional.'">'.($i+1).'</a></li>';
            }
        }

        if($page < $number){
            echo '<li class="page-item"><a class="page-link" href="?page='.($page+1).$additional.'">Next</a></li>';
        }

        echo '</ul>';
    }
//------------
}

function getPwdSecurity($pwd){
    return md5(md5($pwd).MD5_PRIVATE_KEY);
}

function getGET($key){
    $value = '';
    if(isset($_GET[$key])){
        $value = $_GET[$key];
    }
    $value = fixSqlInjection($value);
    return $value;
}

function getPOST($key){
    $value = '';
    if(isset($_POST[$key])){
        $value = $_POST[$key];
    }
    $value = fixSqlInjection($value);
    return $value;
}

function getCOOKIE($key){
    $value = '';
    if(isset($_COOKIE[$key])){
        $value = $_COOKIE[$key];
    }
    return fixSqlInjection($value);
}

function fixSqlInjection($str){
    $str = str_replace("\\", "\\\\", $str);
    $str = str_replace("'", "\'", $str);
    return $str;
}

function authenAdminToken(){
    if(isset($_SESSION['admin'])){
        return $_SESSION['admin'];
    }

    $token = getCOOKIE('token');
    if(empty($token)){
        return null;
    }

    $sql = "select admin.* from admin, admin_token where admin.id = admin_token.id_admin and admin_token.token = '$token'";
    $result = executeResult($sql);

    if($result != null && count($result)>0){
        $_SESSION['admin'] = $result[0];
        return $result[0];
    }
    return null;
}

function authenUserToken(){
    if(isset($_SESSION['user'])){
        return $_SESSION['user'];
    }

    $token = getCOOKIE('token');
    if(empty($token)){
        return null;
    }

    $sql = "select users.* from users, login_token where users.id = login_token.id_user and login_token.token = '$token'";
    $result = executeResult($sql);

    if($result != null && count($result)>0){
        $_SESSION['user'] = $result[0];
        return $result[0];
    }
    return null;
}