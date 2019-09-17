<?php

$file = "export_field.tsv";
header('Content-type: text/tab-separated-values; charset=utf-8');
header("Content-Disposition: attachment; filename=$file");

// Set directory to ROOT
chdir('../');
// Include essencial files
include 'inc/functions.php';

if (!empty($_REQUEST)) {
    $query["query"]["bool"]["must"]["query_string"]["query"] = "type:Record OAI";
    $query["aggs"]["counts"]["terms"]["field"] = 'complete.'.$_REQUEST['field'].'.keyword';
    $query["aggs"]["counts"]["terms"]["order"]["_count"] = "desc";            
    $query["aggs"]["counts"]["terms"]["size"] = 10000;

    $queryFields = ["_id", $_REQUEST['field']];

    //$cursor = $client->search($params);

    $response = Elasticsearch::search($queryFields, 0, $query);
    $content[] = "Faceta\tQuantidade";

    foreach ($response["aggregations"]["counts"]["buckets"] as $facets) {
        
        unset($fields);
        $fields[] = $facets['key'];
        $fields[] = $facets['doc_count'];
        $content[] = implode("\t", $fields);
        unset($fields);                

    }
    echo implode("\n", $content);
}

?>
