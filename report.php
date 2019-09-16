<?php require 'inc/functions.php'; ?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Relatórios</title>
  </head>
  <body>
    <?php require 'inc/navbar.php'; ?>
    <div class="container">   
        <h1>Relatórios</h1>


        <?php 
        
          $result_source = Report::source();
          print_r($result_source);

        if ($result_source["hits"]["total"]["value"] > 0) {
            echo '<h3>Fonte</h3>';
            echo '<ul>';
            echo '<li>Tipo: '.$result_source["hits"]["hits"][0]["_source"]["type"].'</li>';
            echo '<li>Nome da fonte: '.$result_source["hits"]["hits"][0]["_source"]["OAI"]["name"].'</li>';
            echo '<li>Formato de metadados: '.$result_source["hits"]["hits"][0]["_source"]["OAI"]["metadataFormat"].'</li>';
            echo '<li>Data do harvesting: '.$result_source["hits"]["hits"][0]["_source"]["OAI"]["date"].'</li>';
            echo '<li>URL: '.$result_source["hits"]["hits"][0]["_source"]["OAI"]["url"].'</li>';
            if (isset($result_source["hits"]["hits"][0]["_source"]["OAI"]["set"])){
                echo '<li>Set: '.$result_source["hits"]["hits"][0]["_source"]["OAI"]["set"].'</li>';
            }
            echo '</ul>';
        }
        
        ?>

        <br/><br/><br/><br/>

        <?php Report::records(); ?>


    </div>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>