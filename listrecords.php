<?php

require 'inc/functions.php';


if (isset($fields)) {
    $_GET["fields"] = $fields;
}

$i_filter = 0;
if (!empty($_REQUEST['filter'])) {
    foreach ($_REQUEST['filter'] as $filter) {
        $filter_array = explode(":", $filter);
        $filter_array_term = str_replace('"', "", (string)$filter_array[1]);
        if ($filter_array_term == "Não preenchido") {
            $query["query"]["bool"]["must"]["query_string"]["query"] = "-_exists_:".(string)$filter_array[0]."";
        } else {
            $query["query"]["bool"]["filter"][$i_filter]["term"]["complete.".(string)$filter_array[0].".keyword"] = $filter_array_term;
        }
        $i_filter++;
    }
}

if (!empty($_REQUEST['search'])) {
    $query["query"]["bool"]["must"]["query_string"]["query"] = ''.$_REQUEST['search'].'';
}

/* Pagination */
if (isset($_REQUEST['page'])) {
    $page = $_REQUEST['page'];    
} else {
    $page = 1;
}
$limit = 10;
$skip = ($page - 1) * $limit;


$params = [];
$params["index"] = $index;
$params["body"] = $query;
$cursorTotal = $client->count($params);
$total = $cursorTotal["count"];



$params["body"] = $query;
$params["size"] = $limit;
$params["from"] = $skip;
$cursor = $client->search($params);


?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

        <title>Lista de registros</title>

    </head>
    <body>
        <!-- NAV -->
        <?php require 'inc/navbar.php'; ?>
        <!-- /NAV -->

        <br/><br/><br/><br/>

        <main role="main">
            <div class="container">

            <div class="row">
                <div class="col-12">                
                <table class="table">


                    <!-- RECORDS -->
                    <?php

                    foreach ($cursor["hits"]["hits"] as $r) {
                        
                        $keyArray = [];
                        $valueArray[]='<tr>';
                        foreach ($r["_source"]["complete"] as $key => $value) {
                            $keyArray[] = '<th scope="col">'.$key.'</th>';
                            if (is_array($value)){
                                $valueArray[]='<td>'.implode('|', $value).'</td>';
                            } else {
                                $valueArray[]='<td>'.$value.'</td>';
                            }                    
                            
                        }
                        $valueArray[]='</tr>';                     
                    }
                    
                    ?>  
                    <!-- /RECORDS -->

                    <thead>
    <tr>
      <?php print_r(implode('', $keyArray)); ?>
    </tr>
  </thead>
  <tbody>
    <tr>
        <?php print_r(implode('', $valueArray)); ?>
    </tr>
  </tbody>
</table>                    
                                
                
                </div>
            </div>

                       
            </div>
        </main>

        <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>

    

        <!-- JS FILES -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.1.5/js/uikit.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.1.5/js/uikit-icons.min.js"></script>
        <script>
        $('[data-uk-pagination]').on('select.uk.pagination', function(e, pageIndex){
            var url = window.location.href.split('&page')[0];
            window.location=url +'&page='+ (pageIndex+1);
        });
        </script>

    </body>
</html>