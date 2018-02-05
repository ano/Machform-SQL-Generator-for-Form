<?php

    /*
    *	FORM FUNCTIONS
    */	
	 
	function get_form_name($dbh, $form_id){
		$params = array($form_id);
		$query 	= "select 
						 form_name, form_description
					 from 
						 ".MF_TABLE_PREFIX."forms 
					where 
						 form_id = ?
					limit 1";
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		return $row;
	}
	
	function get_form_title($form_id){
		//connect to database
        $dbh 		= mf_connect_db();
        return str_replace(':','',get_form_name($dbh, $form_id)) ; 
	}


    /*
    *	OPTION FUNCTIONS
	*/
	
	function get_options($form_id, $element_id, $option_id){

	}
    
    function get_option_id($element_id, $element_title){
		return element().$element_id . alias($element_title, 1);
	}
    
    /*
	*	ELEMENT FUNCTIONS
	*/

    function get_element_pref_names($dbh, $form_id, $element_id){
		$params = array($form_id, $element_id);
		$elements		= array();
		
		$query 	= "select 
						element_title
					 from 
						 ".MF_TABLE_PREFIX."form_elements
					where 
						 form_id = ? and element_id = ? 
					ORDER BY element_id 
					LIMIT 1";		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		return $row;
	}
	
    function get_elements($dbh, $form_id){
		$params = array($form_id);
		$elements		= array();
		
		$query 	= "select 
						element_id,
						element_title,
						element_type
					 from 
						 ".MF_TABLE_PREFIX."form_elements
					where 
						 form_id = ?
					ORDER BY element_position";		
		$sth = mf_do_query($query,$params,$dbh);
		
		$i=0;
		while($row = mf_do_fetch_result($sth)){
		
			$elements[$i]['element_id'] 	= $row['element_id'];
			$elements[$i]['element_title'] 	= $row['element_title'];
			$elements[$i]['element_type'] 	= $row['element_type'];		
	
			$i++;
		}
		
		return $elements;
	}
	
	function get_element_prefs($dbh, $form_id){
		$params 	= array($form_id);
		$elements	= array();
		
		$query 		= "select 
						element_name
					 from 
						 ".MF_TABLE_PREFIX."column_preferences
					where 
						 form_id = ?
					ORDER BY position";	
	
		$sth = mf_do_query($query,$params,$dbh);
		
		$i=0;
		while($row = mf_do_fetch_result($sth)){
			$elements[$i]		= get_element_id($row['element_name']);
			$i++;
		}
		
		return $elements;	
	}
	
	function get_element_id($element_name){
		$element = str_replace('element_','', $element_name);
		if(substr_count($element,'_')){	
			for($i = 1; $i <=6; $i++){
				$replace = '_'.$i;
				if(substr_count($element, $replace)){
					$element_id = (int) str_replace($replace,'',$element);
				}
			}
		}
		else
			$element_id = (int) $element;		
		return $element_id;		
	}

	function get_preferences($form_id){
		$dbh = mf_connect_db();
		return get_element_prefs($dbh, $form_id);
	}
	
	function get_preference_names($form_id, $element_id){
		$dbh = mf_connect_db();
		return get_element_pref_names($dbh, $form_id, $element_id);
	}

    function get_checkbox_options($dbh, $form_id, $element_id){
		$params = array($form_id, $element_id);
		$query 	= "select 
						 	`option_id`,
							`option`
					 from 
						 ".MF_TABLE_PREFIX."element_options
					where 
						 form_id =? AND element_id =? AND live = 1";
		
		$sth = mf_do_query($query,$params,$dbh);
		$i=0;
		while($row = mf_do_fetch_result($sth)){
		
			$options[$i]['option_id'] 	= $row['option_id'];
			$options[$i]['option'] 		= $row['option'];		
			
			$i++;
		}
		
		return $options;
	}
    
    /*
    * SQL BUILDING FUNCTIONS
    */
	
    function get_all_fields($form_id){
		//connect to database
        $dbh 		= mf_connect_db();
		$elements 	= get_elements($dbh, $form_id);
		$i=0;
		foreach($elements as $element){
			$fields[$i] = $element['element_title'];
			$i++;
		}
		return implode( '","' , str_replace(' ', '_', $fields));
	}
    
    function build_machform_query($form_id){
        
        $join_options	=	null;
        $field_options	=	null;
        $where_options	= 	null;
        $sql = "";

		//Connect to database
        $dbh 			= mf_connect_db();

        //Get elements in a form
        $elements		= get_elements($dbh, $form_id);

        //GENERATE SQL fields for SELECT clause
        $fields 		= implode(", ", get_fields($dbh, $form_id, $elements));
        $field_options	= implode(", ", get_fields_options($elements));
        if($field_options){
            $fields = $field_options . ', ' . $fields;
        }
        $join_options	= get_join_options($form_id, $elements);
        
        //GENERATE SQL JOIN clause for radio, select lookup options 
        $join = '';
        if ($join_options){
            $join = $sql . ' LEFT JOIN 
            '. implode("LEFT JOIN ", $join_options);
        }        
        $where = '';
        if ($where_options){
            $where = $sql . ' 
                '.implode(" ON ", $where_options);
        }
        $sql = '
            SELECT 
                id, date_created, date_updated, ip_address, ' . $fields . ' 
            FROM 
                ap_form_' . $form_id .  
                $join;
        if($where){
            $sql .= '
                ON '
                    .$where;
        }
        
        //GENERATE WHERE clause to select a specific entry by id / entry_id
        if (isset($_GET['entry_id'])){
            $entry_id    = (int) trim($_GET['entry_id']);
            $sql .= "\n\tWHERE `id` = {$entry_id}";
        }
        
        //GENERATE CONDITION clause for fitering records
        if (isset($_GET['filter'])){
            
            $filter_map = array(
                'cs' => 'LIKE',
                'sw' = 'LIKE',
                'ew' = 'LIKE',
                'eq' => '=', 
                'lt' => '<', 
                'gt' => '>', 
                'le' = '<=', 
                'ge' = '>='
            ); //map filter conditions to sql conditions
            
            $filters        = explode(",", $_GET['filter']);
            $filtered_field = mf_sanitize(alpha_num($filters[0]));
            $condition      = mf_sanitize(alpha_num($filters[1]));
            $filter_operand = $filter_map[$condition];
            $filter_value   = mf_sanitize(alpha_num($filters[2]));
            
            //if entry_id is set use an AND clause
            $filter_clause = (isset($_GET['entry_id'])) ? "\n\tAND " : "\n\tHAVING";
            
            //BUILD the condition
            if($condition === "cs"){ // contains string = LIKE                
               
                $sql .= "\n\t{$filter_clause} `{$filtered_field}` {$filter_operand} '%{$filter_value}%'";
                
            }
            else if($condition === "sw"){ // starts with               
               
                $sql .= "\n\t{$filter_clause} `{$filtered_field}` {$filter_operand} '{$filter_value}%'";
                
            }
            else if($condition === "ew"){ // ends with                
               
                $sql .= "\n\t{$filter_clause} `{$filtered_field}` {$filter_operand} '%{$filter_value}'";
                
            }
            else 
                $sql .= "\n\t{$filter_clause} `{$filtered_field}` {$filter_operand}  '{$filter_value}'";
        }
        
        //GENERATE LIMIT clause 
        if (isset($_GET['limit'])){
            $limit = (int) trim($_GET['limit']);
            $sql .= "\n\tLIMIT {$limit}";
        }
        
        //GENERATE ORDER BY clause
        if (isset($_GET['order'])){
            $order_field = mf_sanitize(alpha_num($_GET['order']));
            $order_type = (isset($_GET['order_type'])) ? mf_sanitize(alpha_num($_GET['order_type'])) : 'ASC';
            $sql .= "\n\tORDER BY `{$order_field}` {$order_type}";
        }
        

        
        return $sql;
    }

    function join_on($join_options, $where_options){
        
    }

	function get_shown_fields($form_id){	
        
        $names = null;
        $fields = null;
        
		$prefs = array_unique(get_preferences($form_id));
		$i=0;
		foreach($prefs as $element_id){
			$name = get_preference_names($form_id, $element_id);
			$names[$i] = $name['element_title'];
			$i++;
		}
        
		if($names) $fields = implode('","', str_replace(' ', '_', $names));
        return $fields;
	}
	
    // Return SQL code for Fields
	function get_fields($dbh, $form_id, $elements){
	
		$fields = array();
		
		$i=0;
		foreach ($elements as $element){			
			$element_id = $element['element_id'];
			$element_title = $element['element_title'];
			$element_type = $element['element_type']; 
			
			if('simple_name' == $element_type){ //Simple Name - 2 elements
				$fields[$i] = complex_field($element_id, $element_title, 2);
			}
			else if ('simple_name_wmiddle' == $element_type){ //Simple Name with Middle - 3 elements
				$fields[$i] = complex_field($element_id, $element_title, 3);
			}
			else if ('name' == $element_type){ //Extended Name - 4 elements
				$fields[$i] = complex_field($element_id, $element_title, 4);
			}
			else if ('name_wmiddle' == $element_type){ //Name with Middle - 5 elements
				$fields[$i] = complex_field($element_id, $element_title, 5);
			}
			else if ('address' == $element_type){ //Address - 6	 elements
				$fields[$i] = complex_field($element_id, $element_title, 6);
			}
			else if ('checkbox' == $element_type){ //Checkbox - multiple elements
				$options = get_checkbox_options($dbh, $form_id, $element_id); //get options	
				$fields[$i] = checkbox_fields($element_title, $element_id, $options);
			}
			else if('radio' == $element_type){ 
				$fields[$i] = get_option_id($element_id, $element_title);
			}
			else if('select' == $element_type){ 
				$fields[$i] = get_option_id($element_id, $element_title);
			}
            else if('page_break' == $element_type){
            }
			else { 
				$fields[$i] = element(). $element_id . alias($element_title,0);
			}			
			$i++;
		}
		
		return $fields;
	}
	
    // Return SQL for Checkbox Fields
	function checkbox_fields($element_title, $element_id, $options){	
		$fields = array();
		$i=0;
		foreach($options as $option){
			//e.g. IF(element_8_1 = 1,'Levels_ECE','')
			$element = element();
			$fields[$i] = "
			IF({$element}{$element_id}_{$option['option_id']} = 1, '{$option['option']}', '')"; 
			$i++;
		}		
		return " 
		TRIM(
			CONCAT_WS (' ', 
				". implode(', ', $fields) . "
			)
		) AS `{$element_title}`";
	}
	
    // Return SQL for Complex Fields 
	function complex_field($element_id, $element_title, $num){
		$complex_field = array();
		
		for($i=1; $i<=$num;$i++){
			$complex_field[$i] = element().$element_id. "_{$i}";
		}
		
		return ' 
		CONCAT_WS (" ", 
				'.implode(',',$complex_field).'
			)' . alias($element_title,0);
	}	
	
    // Returns an Alias based on the elements title	
	function alias($element_title,$id){
		$element_title = alpha_num(str_replace(' ', '_', $element_title));
		if($id) $element_title = $element_title . '_ID';
		return ' AS `' . $element_title . '` ';	
	}

    function alpha_num($text){
        return preg_replace("/[^a-zA-Z0-9]/", "", $text);
    }
	
    // Return Prefix element_ for Form Fields
	function element(){
		return ' 
		element_';
	}
	
    // Return SQL JOIN Code for lookup fields - Radio and Select options
	function get_join_options($form_id, $elements){
	
		$join_options = array();
		
		$i=0;
		foreach ($elements as $element){			
			$element_id = $element['element_id'];
			$element_title = $element['element_title'];
			$element_type = $element['element_type']; 
			

			if('radio' == $element_type){ 
				$join_options[$i] = get_join_option($form_id, $element_id, $element_title);
			}
			else if('select' == $element_type){ 
				$join_options[$i] = get_join_option($form_id, $element_id, $element_title);
			}				
			$i++;
		}
		return $join_options;
	}
	
    // Returns an SQL Join Sub-Queries for Radio and Select options
	function get_join_option($form_id, $element_id, $element_title){
		return '
		(
			SELECT	`form_id`,`option_id`,`option` 
			FROM 	'.MF_TABLE_PREFIX.'element_options 
			WHERE `form_id` = '.$form_id.' AND element_id = '.$element_id.' AND live = 1
		)' . alias($element_title,0) . 'ON ap_form_'.$form_id.'.element_'.$element_id.' = `' . str_replace(' ', '_', $element_title) . '`.`option_id`';
	}
	
    // Return SQL WHERE clause for lookup fields - Radio and Select options
	function get_where_options($form_id, $elements){
		$where_options = array();
		
		$i=0;
		foreach ($elements as $element){			
			$element_id = $element['element_id'];
			$element_title = $element['element_title'];
			$element_type = $element['element_type']; 			

			if('radio' == $element_type){ 
				$where_options[$i] = get_where_option($form_id, $element_id, $element_title);
			}
			else if('select' == $element_type){ 
				$where_options[$i] = get_where_option($form_id, $element_id, $element_title);
			}				
			$i++;
		}
		
		return $where_options;
	}
	
    // Returns an SQL WHERE clause for Radio and Select option
	function get_where_option($form_id, $element_id, $element_title){
		return MF_TABLE_PREFIX.'form_'. $form_id .'.element_'.$element_id.' = `'.str_replace(' ', '_', $element_title).'`.`option_id`
               ';
	}
	
    // Return SQL Field names for Radio and Select options
	function get_fields_options($elements){
		$field_options = array();
		
		$i=0;
		foreach ($elements as $element){			
			$element_id = $element['element_id'];
			$element_title = $element['element_title'];
			$element_type = $element['element_type']; 
			

			if('radio' == $element_type){ 
				$field_options[$i] = get_field_option($element_title);
			}
			else if('select' == $element_type){ 
				$field_options[$i] = get_field_option($element_title);
			}				
			$i++;
		}
		return $field_options;			
	}
	
    // Returns an SQL Field name for Radio and Select option
	function get_field_option($element_title){
		return "\n\t\t`" . str_replace(' ', '_', $element_title) . "`.`option` AS `" . str_replace(' ', '_', $element_title) ."`";
	}
    
    /*
    * SQL CREATE FUNCTIONS
    */

	function create_view($title){
		return "CREATE VIEW `" . str_replace(' ', '_', $title['form_name'])  . "` AS ";
	}
	
	function create_table($title){
		return "CREATE TABLE `" . str_replace(' ', '_', $title['form_name'])  . "` AS ";
	}
    
    /*
    * DATA FUNCTIONS
    */
    function get_data($query) {
        $mdb = new MeekroDB(MF_DB_HOST, MF_DB_USER, MF_DB_PASSWORD, MF_DB_NAME);
        return $mdb->query($query);
    }
?>