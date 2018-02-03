# Overview
Takes the form_id for a specific form and generates an SQL query for that form. It can also take an entry_id to limit the generation to a specific entry

# Instructions

Drop this into your hooks folder

You'll then be able to access it via

    http://yourdomain.com/hooks/write.php?form_id=1234

Other paramenters include

    entry_id: the id of an entry record
    order: what field to order by
    limit: what limit to set for the number of returned records
    filter: format => KEY,OPERAND,VALUE e.g. Category,cs,Crew - cs is contains and is equivalent to LIKE
    
    http://yourdomain.com/hooks/write.php?form_id=1234&filter=Category,cs,Crew&limit=30&order=Category
    