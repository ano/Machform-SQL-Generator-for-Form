<?php 

    //Relative path to machforms root folder
    define("BASE_PATH", "../"); 
	define("AUTH_USER",'ffa.fishhacker@gmail.com');
	define("AUTH_PASS", '$2y$10$CtIWOrkAUVuDL3qG5BbdWu1.F7ExDy9RjdyNiY21YcjOZS.o.dw5i'); //one way password hash, generate with password_hash(‘your_password’, PASSWORD_DEFAULT);

    //Load machform files 
    require_once(BASE_PATH . 'config.php');
    require_once(BASE_PATH . 'includes/db-core.php');
    require_once(BASE_PATH . 'includes/helper-functions.php');
    require_once(BASE_PATH . 'includes/filter-functions.php');

    //Load SQL writer library for machform forms
    require_once("lib/write.sql.functions.php");

    //Load MeekroDB for Database Access
    require_once("lib/meekrodb.2.3.class.php");
    require_once("lib/basic.auth.function.php");
