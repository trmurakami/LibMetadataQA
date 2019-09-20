<?php

require '../inc/functions.php';

if (isset($_REQUEST["marcFormat"])) {
    if (isset($_FILES['fileMARC'])) {    
        if (move_uploaded_file($_FILES['fileMARC']['tmp_name'], "../data/marc.seq")) {
            shell_exec('catmandu convert MARC '.$_REQUEST["marcFormat"].' to MARC --type MiJ < ../data/marc.seq > ../data/marc.json');
        }
        unlink("../data/marc.seq");
    } 

}

if (file_exists("../data/marc.json")) {
    $marc_in_json = file("../data/marc.json");
    foreach ($marc_in_json as $line) {
        $marc_array = json_decode($line, true);
        //print("<pre>".print_r($marc_array, true)."</pre>");

        
        foreach ($marc_array["fields"] as $fields){
            if (isset($fields["001"])) {
                $body["doc"]["old_id"] = $fields["001"];
            }            
        } 
        $body["doc"]["type"] = "Record MARC";
        $body["doc"]["complete"] = $marc_array["fields"];
        $body["doc_as_upsert"] = true;
        $id = uuid();
        //print("<pre>".print_r($body, true)."</pre>");
        $result = Elasticsearch::update($id, $body);

    }
    unlink("../data/marc.json");
    sleep(2);
    echo '<script>window.location = \'../index.php\'</script>';
}


?>


