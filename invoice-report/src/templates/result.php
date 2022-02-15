<div class="row mb-4">
    <div class="col-12">
        <table class="table table-hover table-bordered bg-light mb-0">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">ID de factura</th>
                    <th scope="col">Fecha de creación</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Sub Total $</th>
                    <th scope="col">Impuesto $</th>
                    <th scope="col">Total $</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['invoices'] as $invoice) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['id'])?></td>
                        <td><?php echo htmlspecialchars(date_format(date_create($invoice['createdDate']), 'd-m-Y'))?></td>
                        <td><?php echo htmlspecialchars($invoice['companyName'] . $invoice['clientName'])?></td>
                        <td><?php echo htmlspecialchars($invoice['totalUntaxed'])?></td>
                        <td><?php echo htmlspecialchars($invoice['totalTaxAmount'])?></td>
                        <td><?php echo htmlspecialchars($invoice['total'])?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Totales</th>
                    <th></th>
                    <th><?php echo htmlspecialchars($result['cantidadFacturas'])?></th>
                    <th><?php echo htmlspecialchars($result['cantidadSinImpuestos'])?></th>
                    <th><?php echo htmlspecialchars($result['cantidadImpuestos'])?></th>
                    <th><?php echo htmlspecialchars($result['cantidadTotal'])?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>