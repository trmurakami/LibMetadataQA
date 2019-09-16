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

/**
 * Página Inicial
 *
 * @category Class
 * @package  Homepage
 * @author   Tiago Rodrigo Marçal Murakami <tiago.murakami@dt.sibi.usp.br>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://github.com/sibiusp/nav_elastic
 */
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
            echo '<li class="list-group-item"><a href="result.php?filter[]=base:&quot;'.$facets['key'].'&quot;">'.$facets['key'].' ('.number_format($facets['doc_count'], 0, ',', '.').')</a></li>';
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
    static function source()
    {
        $body["query"]["bool"]["must"]["query_string"]["query"] = "type:Repository";
        $response = Elasticsearch::search(null, 10, $body);
        return $response;
    }
    
    static function records()
    {
        global $index;
        global $client;
        $params = ['index' => $index];
        $response = $client->indices()->getMapping($params);
        $facets = new Facets();
        foreach ($response[$index]["mappings"]["properties"]["complete"]["properties"] as $k => $v) {
            $facets->facet($k, 5, $k, null, "_term");
        }

        //$body["query"]["bool"]["must"]["query_string"]["query"] = "type:Record OAI";
        //return $response;
    }
}

Class Facets 
{
    public function facet($field, $size, $field_name, $sort, $sort_type)
    {
        //global $url_base;
        //$query = $this->query;
        $query["query"]["bool"]["must"]["query_string"]["query"] = "type:Record OAI";
        $query["aggs"]["counts"]["terms"]["field"] = "complete.$field.keyword";
        $query["aggs"]["counts"]["terms"]["missing"] = "Não preenchido";
        if (isset($sort)) {
            $query["aggs"]["counts"]["terms"]["order"][$sort_type] = $sort;
        }
        $query["aggs"]["counts"]["terms"]["size"] = $size;
        $response = Elasticsearch::search(null, 0, $query);
        $result_count = count($response["aggregations"]["counts"]["buckets"]);        

        echo '<a href="#" class="list-group-item list-group-item-action active">'.$field_name.'</a>';
        echo '<ul class="list-group list-group-flush">';  
        foreach ($response["aggregations"]["counts"]["buckets"] as $facetResponse) {
            echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
            echo '<a href="result.php?&filter[]='.$field.':&quot;'.str_replace('&', '%26', $facetResponse['key']).'&quot;"  title="E" style="color:#0040ff;font-size: 90%">'.$facetResponse['key'].'</a>
                <span class="badge badge-primary badge-pill">'.number_format($facetResponse['doc_count'], 0, ',', '.').'</span>';
            echo '</li>'; 
        }
        echo '</ul>';              
    }
}



?>