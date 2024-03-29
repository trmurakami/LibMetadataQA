<?php

require '../inc/functions.php';

$file = "tematresReport.tsv";
header('Content-type: text/tab-separated-values; charset=utf-8');
header("Content-Disposition: attachment; filename=$file");

if (!empty($_REQUEST["field"])) {
    $completeField = 'complete.'.$_REQUEST['field'].'.subfields.'.$_REQUEST['subfield'].'';
    $query["query"]["query_string"]["query"] = '_exists_:'.$completeField.'';
    $params = [];
    $params["index"] = $index;
    $params["size"] = 2;
    $params["scroll"] = "30s";
    $params["_source"] = ["_id", "old_id", $completeField];
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

            foreach ($r["_source"]["complete"] as $term) {
                //print_r($term[$_REQUEST['field']]["subfields"][0][$_REQUEST['subfield']]);
                $originalTerm = $term[$_REQUEST['field']]["subfields"][0][$_REQUEST['subfield']];
                $tematresQueryResult = Tests::tematresQuery($term[$_REQUEST['field']]["subfields"][0][$_REQUEST['subfield']], $tematresWebServicesUrl);
                if (!empty($r["_source"]["old_id"])) {
                    $ID = $r["_source"]["old_id"];
                } else {
                    $ID = $r["_id"];
                }
                
                $foundTerm = $tematresQueryResult["foundTerm"];
                $topTerm = $tematresQueryResult["topTerm"];
                $content[] = "$ID\t$originalTerm\t$foundTerm\t$topTerm";
            }

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


