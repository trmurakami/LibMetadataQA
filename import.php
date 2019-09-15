<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Ferramenta de importação de metadados</title>
  </head>
  <body>
    <?php require 'inc/navbar.php'; ?>
    <div class="container">  
        <h1>Ferramenta de importação de metadados</h1>
        <p>Você pode importar os seguintes formatos de metadados</p>
        <ul>
            <li>CSV</li>
            <li>OAI-PMH (Harvesting)</li>
            <li>MARC import</li>
        </ul>

        <h4>Harvesting OAI-PMH</h4>

            <form action="tools/harvester.php" method="POST">
                <div class="form-group">
                    <label for="oai">URL do OAI</label>
                    <input type="text" class="form-control" id="oai" name="oai" placeholder="Digite a URL do OAI">
                </div>
                <div class="form-group">
                    <label for="metadataFormat">Formato de metadados</label>
                    <select class="form-control" id="metadataFormat" name="metadataFormat">
                    <option value="oai_dc" selected>oai_dc</option>
                    <option>2</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>        
        
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>