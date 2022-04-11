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
                    <th scope="col">Plan</th>
                    <th scope="col">Cantidad de clientes por plan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['internetPlans'] as $internetPlan) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($internetPlan['name'])?></td>
                        <td align='right'><?php echo htmlspecialchars($internetPlan['countServices'])?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Totales</th>
                    <th align='right'><?php echo htmlspecialchars($result['countInternetServices'])?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<br>
<div class="row mb-4">
    <div class="col-12">
        <table class="table table-hover table-bordered bg-light mb-0">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Plan</th>
                    <th scope="col">Cantidad de clientes por plan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['generalPlans'] as $generalPlan) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($generalPlan['name'])?></td>
                        <td align='right'><?php echo htmlspecialchars($generalPlan['countServices'])?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Totales</th>
                    <th align='right'><?php echo htmlspecialchars($result['countGeneralServices'])?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>