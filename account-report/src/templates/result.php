<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <p><strong>Reporte generado el 
                    <?php 
                    $dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
                    $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
                    
                    echo $dias[date('w')]." ".date('d')." de ".$meses[date('n')-1]. " del ".date('Y') ;            
                    ?>
                </strong></p>
            </div>
        </div>
    </div>
    <div class="col-12">
        <table class="table table-hover table-bordered bg-light mb-0">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Método de pago</th>
                    <th scope="col">Cantidad de pagos</th>
                    <th scope="col">Montos $</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['paymentMethods'] as $paymentMethod) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($paymentMethod['name'])?></td>
                        <td align='right'><?php echo htmlspecialchars($paymentMethod['count'])?></td>
                        <td align='right'><?php echo htmlspecialchars($paymentMethod['amount'])?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Totales</th>
                    <th align='right'><?php echo htmlspecialchars($result['totalCount'])?></th>
                    <th align='right'><?php echo htmlspecialchars($result['totalAmount'])?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-6">
        <div class="embed-responsive embed-responsive-4by3">
            <div id="chart-total-paid-per-method" class="embed-responsive-item"></div>
        </div>
    </div>
    <div class="col-6">
        <div class="embed-responsive embed-responsive-4by3">
            <div id="chart-count-paids" class="embed-responsive-item"></div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawCharts);

    function drawCharts() {
        drawChart(
            'chart-total-paid-per-method',
            'Total pagado por método de pago',
            [
                ['Payment Method', 'Total paid'],
                <?php
                foreach ($result['paymentMethods'] as $paymentMethod) {
                    printf(
                        '[%s, %F],',
                        json_encode($paymentMethod['name']),
                        $paymentMethod['amount']
                    );
                }
                ?>
            ]
        );

        drawChart(
            'chart-count-paids',
            'Cantidad de pagos por método de pago',
            [
                ['Payment Method', 'Count paids'],
                <?php
                foreach ($result['paymentMethods'] as $paymentMethod) {
                    printf(
                        '[%s, %F],',
                        json_encode($paymentMethod['name']),
                        $paymentMethod['count']
                    );
                }
                ?>
            ]
        );
    }

    function drawChart(id, title, data) {
        var dataTable = google.visualization.arrayToDataTable(data);

        var options = {
            title: title
        };

        var chart = new google.visualization.PieChart(document.getElementById(id));

        chart.draw(dataTable, options);
    }
</script>