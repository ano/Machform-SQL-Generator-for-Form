<?php

	/*
		Author: Ano Tisam
		Email: an0tis@gmail.com
		Website: http://www.whupi.com
		Description: Takes the form_id for a specific form and generates an SQL query for that form. 
                    It can also take an entry_id to limit the generation to a specific entry
		LICENSE: Copyright WHUPI LTD 2018
	*/ 
	
	require_once("init.php");

    require_basic_auth(AUTH_USER, AUTH_PASS);

    if(isset($_GET['form_id']))
    {
        $form_id    = (int) trim($_GET['form_id']);	
        $query      = build_machform_query($form_id);
        
        if ((isset($_GET['format']) && $_GET['format'] === "query")) 
        {
    
            //header('Content-Type: application/json');
            echo json_encode(array('query' => $query));           

        }
        else if ((isset($_GET['format']) && $_GET['format'] === "sql"))
        {
            
            //header('Content-Type: text/plain');
            echo print_r($query);  
        
        }
        else if ((isset($_GET['format']) && $_GET['format'] === "csv"))
        {

            $data = get_data($query);
            //header('Content-Type: text/plain');
            foreach($data as $d){
                $str = implode(",",$d)."\n";
                echo $str;
            } 
            
        }
        else {
            
            $data = get_data($query);            
            //header('Content-Type: application/json');
            echo json_encode(array('data' => $data));
        }
            
    }
    else
    {
        echo "Oops, no form_id parameter provided";
    }  


?>
