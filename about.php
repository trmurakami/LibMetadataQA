<?php

if (isset($_REQUEST["delete"])) {
    if ($_REQUEST["delete"] == "SIM") {
        require 'inc/config.php';
        require 'inc/functions.php';
        // Delete
        $params = ['index' => $index];
        $response = $client->indices()->delete($params);
        header('Location: index.php');
    }
}

?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Sobre o LibMetadataQA</title>
  </head>
  <body>
    <?php require 'inc/navbar.php'; ?>
    <div class="container">   
        <h1>Sobre o LibMetadataQA</h1>

        <h2>Elasticsearch</h2>
        <!-- Button trigger modal delete -->
        <p>Gerenciar operações no Elasticsearch</p>
        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteAllModal">
            Excluir indice no Elasticsearch
        </button>  

        <!-- Modal -->
        <div class="modal fade" id="deleteAllModal" tabindex="-1" role="dialog" aria-labelledby="deleteAllModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAllModalLabel">Confirmação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir todos os registros? Esta operação é irreversível!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <a class="btn btn-danger" href="about.php?delete=SIM" role="button">Excluir tudo</a>
            </div>
            </div>
        </div>
        </div>  




    </div>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>