<?php

require '../inc/functions.php';

if (isset($_REQUEST["marcFormat"])) {
    if (isset($_FILES['fileMARC'])) {    
        if (move_uploaded_file($_FILES['fileMARC']['tmp_name'], "../data/marc.seq")) {
            shell_exec('catmandu convert MARC --type '.$_REQUEST["marcFormat"].' to MARC --type MiJ < ../data/marc.seq > ../data/marc.json');
        }
        unlink("../data/marc.seq");
    } 

}



if (file_exists("../data/marc.json")) {
    $marc_in_json = file("../data/marc.json");
    foreach ($marc_in_json as $line) {
        $marc_array = json_decode($line, true);
        print_r($marc_array);
        echo "<br/><br/>";
    }
    unlink("../data/marc.json");
}


?>


