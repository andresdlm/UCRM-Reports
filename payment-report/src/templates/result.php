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
                </strong></p><br>
                <p>
                <?php 
                    if($result['parameters']['organizationId'] == 0){
                        echo 'En todas las organizaciones';
                    } else {
                        echo 'En la organización ' . $result['organizationName'];
                    }
                ?>, entre  
                <?php echo htmlspecialchars($result['parameters']['createdDateFrom'])?> y 
                <?php echo htmlspecialchars($result['parameters']['createdDateTo'])?> se han registrado un total de 
                <?php echo htmlspecialchars($result['cantidadPagos'])?> pagos 
                <?php 
                    if($result['paymentMethodId'] != '0'){
                        echo 'al método de pago ' . $result['paymentMethodName'] . ',';
                    } else {
                        echo 'a todos los métodos de pago,';
                    }
                ?> que han generado la sumatoria de $
                <?php echo htmlspecialchars($result['cantidadRecibida'])?>.
                </p>

            </div>
        </div>
    </div>

    <div class="col-12">
        <table class="table table-hover table-bordered bg-light mb-0">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">ID de pago</th>
                    <th scope="col">Fecha de registro</th>
                    <th scope="col">ID Cliente</th>
                    <th scope="col">Monto $</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['payments'] as $payment) { ?>
                    <tr>
                        <td><a href="https://<?php echo($result['domain']);?>/crm/billing/payments/<?php echo($payment['id']);?>"><?php echo htmlspecialchars($payment['id']);?></a></td>
                        <td><?php echo htmlspecialchars(date_format(date_create($payment['createdDate']), 'd-m-Y'))?></td>
                        <td><a href="https://<?php echo($result['domain']);?>/crm/client/<?php echo($payment['clientId']);?>"><?php echo htmlspecialchars($payment['clientId']);?></a></td>
                        <td align='right'><?php echo htmlspecialchars($payment['amount'])?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Totales</th>
                    <th></th>
                    <th><?php echo htmlspecialchars($result['cantidadPagos'])?></th>
                    <th align='right'><?php echo htmlspecialchars($result['cantidadRecibida'])?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>