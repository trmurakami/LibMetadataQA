<?php

require ('config.php'); 

/* Show errors */ 
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

/* Load libraries for PHP composer */ 
require (__DIR__.'/../vendor/autoload.php');
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

/* Connect to Elasticsearch */
try {

    $client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();     
    $indexParams['index']  = $index;   
    $testIndex = $client->indices()->exists($indexParams);
} catch (Exception $e) {    
    $error_connection_message = '<div class="alert alert-danger" role="alert">Elasticsearch não foi encontrado.</div>';
}

/* Create index if not exists */

if (isset($testIndex) && $testIndex == false) {
    Elasticsearch::createIndex($index, $client);
    Elasticsearch::mappingsIndex($index, $client);
}

function uuid()
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * Elasticsearch Class
 */
class Elasticsearch
{

    /**
     * Executa o commando get no Elasticsearch
     *
     * @param string   $_id               ID do documento.
     * @param string[] $fields            Informa quais campos o sistema precisa retornar. Se nulo, o sistema retornará tudo.
     * @param string   $alternative_index Caso use indice alternativo
     *
     */
    public static function get($_id, $fields, $alternative_index = "")
    {
        global $index;
        global $type;
        global $client;
        $params = [];

        if (strlen($alternative_index) > 0) {
            $params["index"] = $alternative_index;
        } else {
            $params["index"] = $index;
        }
        $params["id"] = $_id;        
        $params["_source"] = $fields;

        $response = $client->get($params);
        return $response;
    }

    /**
     * Executa o commando search no Elasticsearch
     *
     * @param string[] $fields Informa quais campos o sistema precisa retornar. Se nulo, o sistema retornará tudo.
     * @param int      $size   Quantidade de registros nas respostas
     * @param resource $body   Arquivo JSON com os parâmetros das consultas no Elasticsearch
     *
     */
    public static function search($fields, $size, $body, $alternative_index = "")
    {
        global $index;
        global $client;
        $params = [];

        if (strlen($alternative_index) > 0 ) {
            $params["index"] = $alternative_index;
        } else {
            $params["index"] = $index;
        }

        $params["_source"] = $fields;
        $params["size"] = $size;
        $params["body"] = $body;

        $response = $client->search($params);
        return $response;
    }

    /**
     * Executa o commando update no Elasticsearch
     *
     * @param string   $_id  ID do documento
     * @param resource $body Arquivo JSON com os parâmetros das consultas no Elasticsearch
     *
     */
    public static function update($_id, $body, $alternative_index = "")
    {
        global $index;
        global $client;
        $params = [];

        if (strlen($alternative_index) > 0) {
            $params["index"] = $alternative_index;
        } else {
            $params["index"] = $index;
        }
        $params["id"] = $_id;
        $params["body"] = $body;

        $response = $client->update($params);
        return $response;
    }

    /**
     * Executa o commando delete no Elasticsearch
     *
     * @param string $_id  ID do documento
     *
     */
    public static function delete($_id, $alternative_index = "")
    {
        global $index;
        global $client;
        $params = [];

        if (strlen($alternative_index) > 0) {
            $params["index"] = $alternative_index;
        } else {
            $params["index"] = $index;
        }
        $params["id"] = $_id;
        $params["client"]["ignore"] = 404;

        $response = $client->delete($params);
        return $response;
    }

    /**
     * Executa o commando delete_by_query no Elasticsearch
     *
     * @param string   $_id               ID do documento
     * @param resource $body              Arquivo JSON com os parâmetros das consultas no Elasticsearch
     * @param resource $alternative_index Se tiver indice alternativo
     * 
     * @return array Resposta do comando
     */
    public static function deleteByQuery($_id, $body, $alternative_index = "")
    {
        global $index;
        global $client;
        $params = [];

        if (strlen($alternative_index) > 0) {
            $params["index"] = $alternative_index;
        } else {
            $params["index"] = $index;
        }

        $params["id"] = $_id;
        $params["body"] = $body;

        $response = $client->deleteByQuery($params);
        return $response;
    }

    /**
     * Executa o commando update no Elasticsearch e retorna uma resposta em html
     *
     * @param string   $_id  ID do documento
     * @param resource $body Arquivo JSON com os parâmetros das consultas no Elasticsearch
     *
     */
    static function storeRecord($_id, $body)
    {
        $response = Elasticsearch::update($_id, $body);
        echo '<br/>Resultado: '.($response["_id"]).', '.($response["result"]).', '.($response["_shards"]['successful']).'<br/>';

    }

    /**
     * Cria o indice
     *
     * @param string   $indexName  Nome do indice
     *
     */
    static function createIndex($indexName, $client)
    {
        $createIndexParams = [
            'index' => $indexName,
            'body' => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                    'analysis' => [
                        'filter' => [
                            'portuguese_stop' => [
                                'type' => 'stop',
                                'stopwords' => 'portuguese'
                            ],
                            'my_ascii_folding' => [
                                'type' => 'asciifolding',
                                'preserve_original' => true
                            ],
                            'portuguese_stemmer' => [
                                'type' => 'stemmer',
                                'language' =>  'light_portuguese'
                            ]
                        ],
                        'analyzer' => [
                            'portuguese' => [
                                'tokenizer' => 'standard',
                                'filter' =>  [ 
                                    'lowercase', 
                                    'my_ascii_folding',
                                    'portuguese_stop',
                                    'portuguese_stemmer'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $responseCreateIndex = $client->indices()->create($createIndexParams);
    } 
    
    /**
     * Cria o indice
     *
     * @param string   $indexName  Nome do indice
     *
     */
    static function mappingsIndex($indexName, $client)
    {
        // Set the index and type
        $mappingsParams = [
            'index' => $indexName,
            'body' => [
                'properties' => [
                     'complete' => [
                        'properties' => [
                            'date' => [
                                'type' => 'text',
                                'analyzer' => 'portuguese',
                                'fields' => [
                                    'keyword' => [
                                        'type' => 'keyword',
                                        'ignore_above' => 256
                                    ]
                                ]                                
                            ],
                            '100' => [
                                'properties' => [
                                    'subfields' => [
                                        'properties' => [
                                            'a' => [
                                                'type' => 'text',
                                                'analyzer' => 'portuguese',
                                                'fields' => [
                                                    'keyword' => [
                                                        'type' => 'keyword',
                                                        'ignore_above' => 256
                                                    ]
                                                ] 
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]                                         
                ]
            ]
        ];

        // Update the index mapping
        $client->indices()->putMapping($mappingsParams);
    } 
    

}

class Homepage
{
    static function fieldAgg($field)
    {
        $query = '{
            "aggs": {
                "group_by_state": {
                    "terms": {
                        "field": "'.$field.'.keyword",
                        "size" : 5
                    }
                }
            }
        }';
        $response = Elasticsearch::search(null, 0, $query);
        foreach ($response["aggregations"]["group_by_state"]["buckets"] as $facets) {
            echo '<li class="list-group-item"><a href="report.php">'.$facets['key'].' ('.number_format($facets['doc_count'], 0, ',', '.').')</a></li>';
        }
    }
    
    static function numberOfRecords()
    {
        $body = '
        {
            "query": {
                "match_all": {}
            }
        }
        '; 
        $response = Elasticsearch::search(null, 0, $body);
        return $response["hits"]["total"]["value"];
    }        
}

class Report 
{
    static function sourceType()
    {
        $body["query"]["bool"]["must"]["query_string"]["query"] = "*";
        $response = Elasticsearch::search(null, 1, $body);
        //print("<pre>".print_r($response, true)."</pre>");
        return $response["hits"]["hits"][0]["_source"]["type"];
    }
    
    static function records($type)
    {
        global $index;
        global $client;
        $params = ['index' => $index];
        $response = $client->indices()->getMapping($params);
        $facets = new Facets();
        if ($type == "Record MARC") {
            foreach ($response[$index]["mappings"]["properties"]["complete"]["properties"] as $k => $v) {
                if (isset($v["properties"])) {
                    if (isset($v["properties"]["ind1"])) {
                        $facets->facet($k.".ind1", 5, $k." (Indicador 1)", null, "_term", null);
                    }
                    if (isset($v["properties"]["ind2"])) {
                        $facets->facet($k.".ind2", 5, $k." (Indicador 2)", null, "_term", null);
                    }
                    if (isset($v["properties"]["subfields"])) {
                        foreach ($v["properties"]["subfields"]["properties"] as $k_prop => $v_prop) {
                            $facets->facet($k.".subfields.".$k_prop, 5, $k." Subcampo ".$k_prop, null, "_term", $k, $k_prop);
                        }
                        
                    }                                         
                } else {
                    $facets->facet($k, 5, $k, null, "_term");
                }
            }
        } else {
            foreach ($response[$index]["mappings"]["properties"]["complete"]["properties"] as $k => $v) {
                $facets->facet($k, 5, $k, null, "_term");
            }
        }

    }
}

Class Facets 
{
    public function facet($field, $size, $field_name, $sort, $sort_type, $fieldNumber = null, $subfieldNumber = null)
    {
        $query["query"]["bool"]["must"]["query_string"]["query"] = "*";
        $query["aggs"]["counts"]["terms"]["field"] = "complete.$field.keyword";
        $query["aggs"]["counts"]["terms"]["missing"] = "Não preenchido";
        if (isset($sort)) {
            $query["aggs"]["counts"]["terms"]["order"][$sort_type] = $sort;
        }
        $query["aggs"]["counts"]["terms"]["size"] = $size;
        $response = Elasticsearch::search(null, 2, $query);
        $result_count = count($response["aggregations"]["counts"]["buckets"]);
        
        echo '<div class="card text-center">
        <div class="card-header bg-info">
          Campo: '.$field_name.'
        </div>
        <div class="card-body">';

        echo '<div class="d-flex bd-highlight">
                <div class="p-2 flex-grow-1 bd-highlight">';

        echo '<ul class="list-group list-group-flush">';  
        foreach ($response["aggregations"]["counts"]["buckets"] as $facetResponse) {
            echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
            echo '<a href="listrecords.php?&filter[]='.$field.':&quot;'.str_replace('&', '%26', $facetResponse['key']).'&quot;"  title="E" style="color:#0040ff;font-size: 90%">'.$facetResponse['key'].'</a>
                <span class="badge badge-primary badge-pill">'.number_format($facetResponse['doc_count'], 0, ',', '.').'</span>';
            echo '</li>';
        }
        echo '</ul>';        
        $numberMissing = Tests::countMissing($field_name);
        if ($numberMissing == 0) {
            $alertClass = "alert-success"; 
        } else {
            $alertClass = "alert-warning"; 
        }
        echo '</div>';
        echo '<div class="p-2 bd-highlight">';        
        echo '<div class="alert '.$alertClass.'" role="alert"><a href="listrecords.php?&search=-_exists_:complete.'.$field_name.'.keyword">Registros com o campo '.$field_name.' não preenchido: '.$numberMissing.'</a></div>';
        $numberAgg = Tests::countAgg($field_name);
        echo '<p>Número de valores únicos no campo: '.$numberAgg.'</p>';
        echo '<p><a href="tools/export.php?field='.$field_name.'">Exportar valores do campo '.$field_name.'</a></p>';
        if (!is_null($fieldNumber)) {
            echo '<p><a href="tools/tematres_report.php?field='.$fieldNumber.'&subfield='.$subfieldNumber.'">Gerar relatório de correspondência no Tematres para o campo '.$field_name.'</a></p>';
        }        
        echo '</div>';
        echo '</div></div></div>';         
    }
}

Class Tests 
{
    public static function countMissing($field) {
        global $index;
        global $client;
        $query["query"]["bool"]["must"]["query_string"]["query"] = "-_exists_:complete.$field.keyword";
        $params = [];
        $params["index"] = $index;
        $params["body"] = $query;
        $cursorTotal = $client->count($params);
        $total = $cursorTotal["count"];                
        return $total;
    }
    public static function countAgg($field) {

        $query["aggs"]["type_count"]["cardinality"]["field"] = "complete.$field.keyword";
        $responseAgg = Elasticsearch::search(null, 0, $query);
        return $responseAgg["aggregations"]["type_count"]["value"];
    }
    
    public static function tematresQuery($term, $tematresWebServicesUrl)
    {
        // Clean term
        $term = preg_replace("/\s+/", " ", $term);
        $clean_term = str_replace(array("\r\n", "\n", "\r"), "", $term);
        $clean_term = preg_replace('/^\s+|\s+$/', '', $clean_term);
        $clean_term = str_replace("\t\n\r\0\x0B\xc2\xa0", " ", $clean_term);
        $clean_term = trim($clean_term, " \t\n\r\0\x0B\xc2\xa0");
        $clean_term = rawurlencode($clean_term);
        $clean_term_p = $term;
        $clean_term = str_replace("%C2%A0", "%20", $clean_term);
        $clean_term = str_replace("&", "e", $clean_term);
        // Query tematres
        $ch = curl_init();
        $method = "GET";
        $url = ''.$tematresWebServicesUrl.'?task=fetch&arg='.$clean_term.'&output=json';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        $result_get_id_tematres = curl_exec($ch);
        $resultado_get_id_tematres = json_decode($result_get_id_tematres, true);
        print_r($resultado_get_id_tematres["resume"]["cant_result"]);
        curl_close($ch);
        // Get correct term
        if ($resultado_get_id_tematres["resume"]["cant_result"] != 0) {
            foreach ($resultado_get_id_tematres["result"] as $key => $val) {
                $term_key = $key;
            }
            $ch = curl_init();
            $method = "GET";
            $url = ''.$tematresWebServicesUrl.'?task=fetchTerm&arg='.$term_key.'&output=json';
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            $result_term = curl_exec($ch);
            $resultado_term = json_decode($result_term, true);
            $foundTerm = $resultado_term["result"]["term"]["string"];
            $termNotFound = "";
            curl_close($ch);
            $ch_country = curl_init();
            $method = "GET";
            $url_country = ''.$tematresWebServicesUrl.'?task=fetchUp&arg='.$term_key.'&output=json';
            curl_setopt($ch_country, CURLOPT_URL, $url_country);
            curl_setopt($ch_country, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch_country, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            $result_country = curl_exec($ch_country);
            $resultado_country = json_decode($result_country, true);
            foreach ($resultado_country["result"] as $country_list) {
                if ($country_list["order"] == 1) {
                    $topTerm = $country_list["string"];
                }
            }
            curl_close($ch_country);
        } else {
            $termNotFound = $clean_term_p;
            $foundTerm = "";
            $topTerm = "ND";
        }
        return compact('foundTerm', 'termNotFound', 'topTerm');
    }    
}



?>