# Overview
Takes the **form_id** for a specific form and generates an SQL query for that form. It can also take an **entry_id** to limit the sql generated to focus on a specific entry. See the Parameters section for more parameters that can be used.

# Instructions

Drop this into your **hooks** folder

You'll then be able to access it via

    http://yourdomain.com/hooks/write.php?form_id=1234

You will be prompted for a username and password (uses BASIC AUTH). The default username and password is:

- **Username**: administrator
- **Password**: $2y$10$CtIWOrkAUVuDL3qG5BbdWu1.F7ExDy9RjdyNiY21YcjOZS.o.dw5i

Make sure to change the default password. The password is in the init.php file.

# Parameters

Other paramenters include

- **entry_id**: the id of an entry record
- **order**: what field to order by
- **limit**: what limit to set for the number of returned records
- **filter**: format => KEY,OPERAND,VALUE e.g. &filter=Category,cs,Crew 

    **KEY**: The field you want filtered

    **OPERAND**: The filter operation that is being applied

        cs - contain string (string contains value)
        eq - equal (string or number matches exactly)

    **VALUE**: The value that is being filtered

**format**: what format should this be outputted in 
    
        sql: JSON response of the SQL Query 
        query: Plain Text response of the Query
        json (DEFAULT): JSON reponse of the data
    
Example

    http://yourdomain.com/hooks/write.php?form_id=1234&filter=Category,cs,Crew&limit=30&order=Category

# Show SQL 
Default behaviour is to show you the data of a form in JSON format. To show the **SQL QUERY** add 

    &format=sql 

to the URL parameter OR

    &format=query
    
to return a json of the query

# URL
A complete example is 

    http://yourdomain.com/hooks/write.php?form_id=1234&filter=Category,cs,Crew&limit=30&order=Category&format=sql


    