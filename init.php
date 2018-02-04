<?php 

    
    define("BASE_PATH", "../"); //Relative path to machforms root folder
	define("AUTH_USER",'administrator'); //BASIC AUTH Username
	define("AUTH_PASS", '$2y$10$CtIWOrkAUVuDL3qG5BbdWu1.F7ExDy9RjdyNiY21YcjOZS.o.dw5i'); //BASIC AUTH Password

    //Load Machform files 
    require_once(BASE_PATH . 'config.php');
    require_once(BASE_PATH . 'includes/db-core.php');
    require_once(BASE_PATH . 'includes/helper-functions.php');
    require_once(BASE_PATH . 'includes/filter-functions.php');

    //Load SQL writer library for machform forms
    require_once("lib/write.sql.functions.php");

    //Load MeekroDB for Database Access
    require_once("lib/meekrodb.2.3.class.php");

    //Load library if you want to authenticate using BASIC AUTH
    require_once("lib/basic.auth.function.php");
