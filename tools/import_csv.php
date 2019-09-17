<?php

require '../inc/functions.php';

if (isset($_FILES['fileCSV'])) {

    $fh = fopen($_FILES['fileCSV']['tmp_name'], 'r+');
    $row = fgetcsv($fh, 108192, "\t");

    foreach ($row as $key => $value) {
        $rowNum[$key] = $value;
    }


    while (($row = fgetcsv($fh, 108192, "\t")) !== false) {
        $doc = Record::Build($row, $rowNum);
        $id = uuid();
        $result = Elasticsearch::update($id, $doc);
        flush();    

    }
    
}

sleep(2);
echo '<script>window.location = \'../index.php\'</script>';

class Record
{
    public static function build($row, $rowNum)
    {
        foreach ($row as $key=>$value) {
            $body["doc"]["complete"][$rowNum[$key]] = $value;
        }
        
        $body["doc"]["old_id"] = $row[0];
        $body["doc"]["type"] = "Record CSV";
        $body["doc_as_upsert"] = true;
        return $body;
    }
}

?>


