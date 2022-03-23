<div class="row mb-4">
    <div class="col-12">
        <table class="table table-hover table-bordered bg-light mb-0">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">ID de factura</th>
                    <th scope="col">Fecha de vencimiento</th>
                    <th scope="col">ID Cliente</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Sub Total $</th>
                    <th scope="col">Impuesto $</th>
                    <th scope="col">Total $</th>
                    <th scope="col">Total sin pagar $</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['invoices'] as $invoice) { ?>
                    <tr>
                        <td><a href="https://<?php echo($result['domain']);?>/crm/billing/invoice/<?php echo($invoice['id']);?>"><?php echo htmlspecialchars($invoice['id'])?></a></td>
                        <td><?php echo htmlspecialchars(date_format(date_create($invoice['dueDate']), 'd-m-Y'))?></td>
                        <td><a href="https://<?php echo($result['domain']);?>/crm/client/<?php echo($invoice['clientId']);?>"><?php echo htmlspecialchars($invoice['clientId']);?></a></td>
                        <td><?php echo htmlspecialchars($invoice['clientCompanyName'] . $invoice['clientFirstName'] . ' ' . $invoice['clientLastName'])?></td>
                        <td align='right'><?php echo htmlspecialchars($invoice['totalUntaxed'])?></td>
                        <td align='right'><?php echo htmlspecialchars($invoice['totalTaxAmount'])?></td>
                        <td align='right'><?php echo htmlspecialchars($invoice['total'])?></td>
                        <td align='right'><?php echo htmlspecialchars($invoice['amountToPay'])?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Totales</th>
                    <th></th>
                    <th></th>
                    <th><?php echo htmlspecialchars($result['cantidadFacturas'])?></th>
                    <th align='right'><?php echo htmlspecialchars($result['cantidadSinImpuestos'])?></th>
                    <th align='right'><?php echo htmlspecialchars($result['cantidadImpuestos'])?></th>
                    <th align='right'><?php echo htmlspecialchars($result['cantidadTotal'])?></th>
                    <th align='right'><?php echo htmlspecialchars($result['cantidadTotalSinPagar'])?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>