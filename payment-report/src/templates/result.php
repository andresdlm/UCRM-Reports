<div class="row mb-4">
    <div class="col-12">
        <table class="table table-hover table-bordered bg-light mb-0">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">ID de pago</th>
                    <th scope="col">Fecha de registro</th>
                    <th scope="col">Método de pago</th>
                    <th scope="col">ID Cliente</th>
                    <th scope="col">Monto $</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['payments'] as $payment) { ?>
                    <tr>
                        <td><a href="https://<?php echo($result['domain']);?>/crm/billing/payments/<?php echo($payment['id']);?>"><?php echo htmlspecialchars($payment['id']);?></a></td>
                        <td><?php echo htmlspecialchars(date_format(date_create($payment['createdDate']), 'd-m-Y'))?></td>
                        <td><?php echo htmlspecialchars($payment['methodId'])?></td>
                        <td><a href="https://<?php echo($result['domain']);?>/crm/client/<?php echo($payment['clientId']);?>"><?php echo htmlspecialchars($payment['clientId']);?></a></td>
                        <td><?php echo htmlspecialchars($payment['amount'])?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Totales</th>
                    <th></th>
                    <th></th>
                    <th><?php echo htmlspecialchars($result['cantidadPagos'])?></th>
                    <th><?php echo htmlspecialchars($result['cantidadRecibida'])?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>