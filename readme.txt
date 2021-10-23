create table users(
    id int primary key auto_increasement,
    fullname varchar(50),
    username varchar(50),
    email varchar(150),
    address varchar(200),
    password varchar(50),
    phonenumber varchar(12)
)

create table login_token(
    id_user int references users (id), 
    token varchar(50) not null unique,
    primary key (id_user, token)
)

Authen:
    API:    login
            - URL: api/authen.php
            - Method: POST
            - Request: {
                "action": "login",
                "email": "",
                "password": ""
            }
            - Response: {
                "status": 1 (1: success, -1:fail),
                "msg": ""
            }

    API:    logout
            - URL: api/authen.php
            - Method: POST
            - Request: {
                "action": "logout"
            }
            - Response: {
                "status": 1 (1: success, -1:fail),
                "msg": ""
            }  

    API:    register
            - URL: api/authen.php
            - Method: POST
            - Request: {
                "action": "register",
                "username": "",
                "email": "",
                "password": "",
                "address": "",
                "phonenumber": ""
            }
            - Response: {
                "status": 1 (1: success, -1:fail),
                "msg": ""
            }

    API:    userList
            - URL: api/authen.php
            - Method: POST
            - Request: {
                "action": "list"
            }
            - Response: {
                "status": 1 (1: success, -1:fail),
                "msg": ""
                "userList": [
                    {
                        "id": "",
                        "fullname": "",
                        "username": "",
                        "email": "",
                        "address": "",
                        "phonenumber": ""
                    }
                ]
            } 
                 
