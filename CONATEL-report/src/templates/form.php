<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte de servicios</title>
    <link rel="stylesheet" href="<?php echo rtrim(htmlspecialchars($ucrmPublicUrl, ENT_QUOTES), '/'); ?>/assets/fonts/lato/lato.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="public/main.css">
</head>
<body>
    <div id="header">
        <h1>Reporte de servicios</h1>
    </div>
    <div id="content" class="container container-fluid ml-0 mr-0">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form id="report-form">
                            <div class="form-row align-items-end">

                                <div class="col-3">
                                    <label class="mb-0" for="frm-until"><small>Hasta:</small></label>
                                    <input type="date" name="until" id="frm-until" placeholder="YYYY-MM-DD" class="form-control form-control-sm" value="<?php echo htmlspecialchars($result['until'] ?? '', ENT_QUOTES); ?>">
                                </div>

                                <div class="col-auto ml-auto">
                                    <button id="btn-submit" type="submit" class="btn btn-primary btn-sm pl-4 pr-4">Generar</button>
                                    <span id="btn-loading" class="d-none btn btn-primary btn-sm pl-4 pr-4 disabled">
                                        Generando...
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <div id="content" class="container container-fluid ml-0 mr-0">
        <?php
            if ($result) {
                require_once(__DIR__ . '/result.php');
            }
        ?>
    </div>
    
    <script>
        document.querySelector("#report-form").addEventListener("submit", function(e){
            document.querySelector("#btn-submit").classList.add('d-none');
            document.querySelector("#btn-loading").classList.remove('d-none');
        });
    </script>
</body>
</html>
