<script type="text/javascript" src="js/visorPedidos.js"></script>
<input type="hidden" name="FechaInicialV" id="FechaInicialV" value="<?= $_REQUEST["FechaInicialV"] ?>">
<input type="hidden" name="FechaFinalV" id="FechaFinalV" value="<?= $_REQUEST["FechaFinalV"] ?>">
<table  class="DiseñoTabla" cellpadding="0" cellspacing="0" summary="Visor de pedidos">
    <tr class="HederPedidos">
        <th style="width: 29%;"> </th>
        <th style="width: 35%;text-align: center;">Pedidos</th>
        <th style="width: 35%;text-align: center;">Detalle</th>
    </tr>
    <tr>
        <td valign="top">
            <div class="PedidosClass" id="PP">Pedidos pendientes</div>
            <div class="PedidosClass" id="PA">Pedidos aceptados</div>
            <div class="PedidosClass" id="PEP">Pedidos en proceso</div>
            <div class="PedidosClass" id="PC">Pedidos en cancelados</div>
        </td>
        <td valign="top">
            <div id="ConecntPedidos"></div>
        </td>
        <td valign="top">
            <div id="ConecntDetalle"></div>
        </td>
    </tr>
</table>
<style>
    .DiseñoTabla{
        width: 100%;
        margin-top: 10px;
        border-radius: 15px;
        border: 1px solid appworkspace;
        background-color: #D5D8DC;
    }
    .HederPedidos{
        font-size: 18px;
        font-family: sans-serif;
        background: #ff6633;
        color: #F8F9F9;
        font-weight: bold;
    }
    .PedidosClass{
        margin: 10%;
        border: 1px solid #099;
        height: 60px;
        font-size: 18px;
        padding-left:  23px;
        padding-top:  18px;
        color: #F8F9F9;
        width: 200px;
        border-radius: 15px;
        background-color: #099;
    }
    .PedidosClass:hover{
        background-color: #28B463;
    }
    .PedidosClass2{
        margin: 5px;
        font-size: 12px;
        border: 1px solid #099;
        height: 60px;
        padding: 5px;
        color: #F8F9F9;
        width: 90%;
        margin-left: 4%;
        border-radius: 5px;
        background-color: #099;
    }
    .PedidosClass2:hover{
        background-color: #28B463;
    }
    .PedidosClass3{
        margin: 5px;
        margin-top: 25%;
        font-size: 16px;
        border: 1px solid #099;
        height: 65%;
        padding: 5px;
        color: #F8F9F9;
        width: 90%;
        margin-left: 4%;
        border-radius: 5px;
        background-color: #099;
    }
    #scroll{
        border:0px solid;
        max-height: 380px;
        width:100%;
        overflow-y:scroll;
        overflow-x:hidden;
    }
</style>