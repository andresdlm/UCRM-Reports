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
                    if($result['organizationId'] == 0){
                        echo 'En todas las organizaciones';
                    } else {
                        echo 'En la organización ' . $result['organizationName'];
                    }
                ?>, entre 
                <?php echo htmlspecialchars($result['registrationDateFrom'])?> y 
                <?php echo htmlspecialchars($result['registrationDateTo'])?> se han activado el total de 
                <?php echo htmlspecialchars($result['clientsCount'])?> clientes 
                <?php 
                    if($result['clientType'] == 1) {
                        echo 'de tipo residencial '; 
                    } else if ($result['clientType'] == 2) {
                        echo 'de tipo empresarial ';
                    }
                ?>
                que han generado la cantidad 
                de nuevos ingresos de $<?php echo htmlspecialchars(round($result['plansTotalPrice'], 2))?>.
                </p>
            </div>
        </div>
    </div>

    <div class="col-12">
        <table class="table table-hover table-bordered bg-light mb-0">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nombre de cliente</th>
                    <th scope="col">Tipo de cliente</th>
                    <th scope="col">Fecha de Activación</th>
                    <th scope="col">Plan</th>
                    <th scope="col">Precio del plan sin iva</th>
                    <th scope="col">Vendedor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['clients'] as $client) { ?>
                    <tr>
                        <td><a href="https://<?php echo($result['domain']);?>/crm/client/<?php echo($client['id']);?>"><?php echo htmlspecialchars($client['id']);?></a></td>
                        <td><?php echo htmlspecialchars($client['companyName'] . $client['firstName'] . ' ' . $client['lastName']); ?></td>
                        <td><?php if($client['clientType'] == 1) {echo htmlspecialchars("Residencial");} else {echo htmlspecialchars("Empresarial");}?></td>
                        <td><?php echo htmlspecialchars(date_format(date_create($client['activeFrom']), 'd-m-Y'))?></td>
                        <td><?php echo htmlspecialchars($client['servicePlanName'])?></td>
                        <td><?php echo htmlspecialchars(round($client['servicePlanPrice'], 2))?></td>
                        <td><?php echo htmlspecialchars($client['referral'])?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Totales</th>
                    <th><?php echo htmlspecialchars($result['clientsCount'])?></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th><?php echo htmlspecialchars(round($result['plansTotalPrice'], 2))?></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>