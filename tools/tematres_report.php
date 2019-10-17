<?php

require '../inc/functions.php';

//$file = "tematresReport.tsv";
//header('Content-type: text/tab-separated-values; charset=utf-8');
//header("Content-Disposition: attachment; filename=$file");

if (!empty($_REQUEST["field"])) {
    $query["query"]["query_string"]["query"] = '_exists_:'.$_REQUEST['field'].'';
    $params = [];
    $params["index"] = $index;
    $params["size"] = 2;
    $params["scroll"] = "30s";
    $params["_source"] = ["_id", $_REQUEST["field"]];
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

            print_r($r);

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


