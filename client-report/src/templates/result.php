<div class="row mb-4">
    <div class="col-12">
        <table class="table table-hover table-bordered bg-light mb-0">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nombre de cliente</th>
                    <th scope="col">Tipo de cliente</th>
                    <th scope="col">Fecha de Registro</th>
                    <th scope="col">Vendedor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['clients'] as $client) { ?>
                    <tr>
                        <td><a href="https://<?php echo($result['domain']);?>/crm/client/<?php echo($client['id']);?>"><?php echo htmlspecialchars($client['id']);?></a></td>
                        <td><?php echo htmlspecialchars($client['firstName'] . ' ' . $client['lastName']); ?></td>
                        <td><?php echo htmlspecialchars($client['clientType'])?></td>
                        <td><?php echo htmlspecialchars(date_format(date_create($client['registrationDate']), 'd-m-Y'))?></td>
                        <td><?php echo htmlspecialchars($client['referral'])?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Total de clientes</th>
                    <th><?php echo htmlspecialchars($result['cantidadClientes'])?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>