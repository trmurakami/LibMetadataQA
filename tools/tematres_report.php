<?php

require '../inc/functions.php';

//$file = "tematresReport.tsv";
//header('Content-type: text/tab-separated-values; charset=utf-8');
//header("Content-Disposition: attachment; filename=$file");

if (!empty($_REQUEST["field"])) {
    $completeField = 'complete.'.$_REQUEST['field'].'.subfields.'.$_REQUEST['subfield'].'';
    $query["query"]["query_string"]["query"] = '_exists_:'.$completeField.'';
    $params = [];
    $params["index"] = $index;
    $params["size"] = 2;
    $params["scroll"] = "30s";
    $params["_source"] = ["_id", $completeField];
    $params["body"] = $query;

    $cursor = $client->search($params);

    $content[] = "ID\tTermo atual\tTermo encontrado\tTermo Top";

    while (isset($cursor['hits']['hits']) && count($cursor['hits']['hits']) > 0) {
        $scroll_id = $cursor['_scroll_id'];
        $cursor = $client->scroll(
            [
            "scroll_id" => $scroll_id,
            "scroll" => "30s"
            ]
        );

        foreach ($cursor["hits"]["hits"] as $r) {

            print_r($r["_source"]["complete"]);
            echo "<br/><br/>";

            //unset($fields);

            //$fields[] = $r['_id'];

            // if (!empty($r["_source"]['USP']['titleSearchCrossrefDOI'])) {
            //     $fields[] = $r["_source"]['USP']['titleSearchCrossrefDOI'];
            // } else {
            //     $fields[] = "";
            // }

            // $content[] = implode("\t", $fields);
            // unset($fields);


        }
    }
    echo implode("\n", $content);

}


?>


