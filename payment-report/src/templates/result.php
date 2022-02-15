<div class="row mb-4">
    <div class="col-12">
        <table class="table table-hover table-bordered bg-light mb-0">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">ID de pago</th>
                    <th scope="col">Fecha de registro</th>
                    <th scope="col">Método de pago</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Monto $</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['payments'] as $payment) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($payment['id'])?></td>
                        <td><?php echo htmlspecialchars(date_format(date_create($payment['createdDate']), 'd-m-Y'))?></td>
                        <td><?php echo htmlspecialchars($payment['methodId'])?></td>
                        <td><?php echo htmlspecialchars($payment['companyName'] . $payment['clientName'])?></td>
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