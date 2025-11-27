<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: login_adm.php");
    exit();
}

$conn = new mysqli("localhost","root","","ananails");
if ($conn->connect_error) die("Erro de conexão: " . $conn->connect_error);

// Atualizar status
if(isset($_GET['acao']) && isset($_GET['id'])){
    $acao = $_GET['acao'];
    $id = intval($_GET['id']);

    if($acao === 'confirmar'){
        $conn->query("UPDATE AGENDAMENTO SET Status='Confirmado' WHERE Cod_agendamento=$id");
    } elseif($acao === 'cancelar'){
        $conn->query("UPDATE AGENDAMENTO SET Status='Cancelado' WHERE Cod_agendamento=$id");
    }
    header("Location: agenda_adm.php");
    exit();
}

// Filtro e pesquisa
$statusFiltro = $_GET['status'] ?? 'Todos';
$pesquisa = $_GET['pesquisa'] ?? '';

// Construir query dinâmica
$where = [];
if($statusFiltro !== 'Todos'){
    $where[] = "A.Status='$statusFiltro'";
}
if(!empty($pesquisa)){
    $pesquisa = $conn->real_escape_string($pesquisa);
    $where[] = "(U.Nome_usuario LIKE '%$pesquisa%' OR S.Nome_servico LIKE '%$pesquisa%')";
}

$whereSQL = "";
if(count($where) > 0){
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

$sql = "SELECT 
            A.Cod_agendamento, 
            U.Nome_usuario AS cliente, 
            S.Nome_servico AS servico, 
            A.Data_agendamento, 
            A.Hora_agendamento, 
            A.Status
        FROM AGENDAMENTO A
        INNER JOIN USUARIO U ON A.Cod_usuario = U.Cod_usuario
        INNER JOIN SERVICO S ON A.Cod_servico = S.Cod_servico
        $whereSQL
        ORDER BY A.Data_agendamento, A.Hora_agendamento";

$result = $conn->query($sql);
if(!$result){
    die("Erro na consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Agenda Administrador</title>
<link rel="stylesheet" href="styleadm.css">
<style>
.agenda-container {
    max-width: 1100px;
    margin: 50px auto;
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    font-family: Arial, sans-serif;
}
.agenda-container h2 {
    margin-bottom: 20px;
    color: #333;
}
.logout {
    float: right;
    text-decoration: none;
    color: #fff;
    background: #dc3545;
    padding: 6px 12px;
    border-radius: 6px;
    transition: 0.3s;
}
.logout:hover { background: #c82333; }

form#filtroForm {
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}
form#filtroForm select, form#filtroForm input {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
}
form#filtroForm button {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    background-color: #007bff;
    color: #fff;
    cursor: pointer;
    font-size: 14px;
    transition: 0.3s;
}
form#filtroForm button:hover { background-color: #0069d9; }

/* Tabela */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
th, td {
    padding: 12px 15px;
    text-align: center;
}
th {
    background-color: #007bff;
    color: #fff;
    text-transform: uppercase;
    font-weight: 500;
}
tr {
    border-bottom: 1px solid #ddd;
    transition: 0.3s;
}
tr:hover { background-color: #f1f1f1; }

/* Cores por status */
.status-confirmado { background-color: #d4edda; }
.status-cancelado  { background-color: #f8d7da; }
.status-pendente   { background-color: #fff3cd; }

/* Botões */
.btn-acao {
    padding: 6px 12px;
    margin: 0 2px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    color: #fff;
    text-decoration: none;
    display: inline-block;
    transition: 0.3s;
}
.btn-confirmar { background-color: #28a745; }
.btn-confirmar:hover { background-color: #218838; }
.btn-cancelar { background-color: #dc3545; }
.btn-cancelar:hover { background-color: #c82333; }
.btn-disabled { background-color: #6c757d; cursor: not-allowed; }

/* Responsividade */
@media(max-width: 768px){
    th, td { font-size: 12px; padding: 8px; }
    .btn-acao { font-size: 12px; padding: 4px 8px; }
    .logout { padding: 4px 8px; font-size: 12px; }
    form#filtroForm { flex-direction: column; align-items: flex-start; }
}
</style>
</head>
<body>

<div class="agenda-container">
    <h2>Agenda do Administrador - <?php echo $_SESSION['admin_nome']; ?></h2>
    <a href="logout.php" class="logout">Sair</a>

    <form id="filtroForm" method="GET">
        <label>Status:
            <select name="status">
                <option value="Todos" <?php if($statusFiltro=='Todos') echo 'selected'; ?>>Todos</option>
                <option value="Pendente" <?php if($statusFiltro=='Pendente') echo 'selected'; ?>>Pendente</option>
                <option value="Confirmado" <?php if($statusFiltro=='Confirmado') echo 'selected'; ?>>Confirmado</option>
                <option value="Cancelado" <?php if($statusFiltro=='Cancelado') echo 'selected'; ?>>Cancelado</option>
            </select>
        </label>
        <label>Pesquisar:
            <input type="text" name="pesquisa" placeholder="Cliente ou Serviço" value="<?php echo htmlspecialchars($pesquisa); ?>">
        </label>
        <button type="submit">Filtrar</button>
    </form>

    <table>
        <tr>
            <th>Cliente</th>
            <th>Serviço</th>
            <th>Data</th>
            <th>Hora</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>

        <?php
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $classeStatus = '';
                if($row['Status'] === 'Confirmado') $classeStatus = 'status-confirmado';
                elseif($row['Status'] === 'Cancelado') $classeStatus = 'status-cancelado';
                else $classeStatus = 'status-pendente';

                echo "<tr class='{$classeStatus}'>
                        <td>".htmlspecialchars($row['cliente'])."</td>
                        <td>".htmlspecialchars($row['servico'])."</td>
                        <td>".htmlspecialchars($row['Data_agendamento'])."</td>
                        <td>".htmlspecialchars($row['Hora_agendamento'])."</td>
                        <td>".htmlspecialchars($row['Status'])."</td>
                        <td>";

                // Botões inteligentes
                if($row['Status'] === 'Confirmado'){
                    echo "<a class='btn-acao btn-disabled'>Confirmado</a>";
                    echo "<a class='btn-acao btn-cancelar' href='?acao=cancelar&id={$row['Cod_agendamento']}'>Cancelar</a>";
                } elseif($row['Status'] === 'Cancelado'){
                    echo "<a class='btn-acao btn-confirmar' href='?acao=confirmar&id={$row['Cod_agendamento']}'>Confirmar</a>";
                    echo "<a class='btn-acao btn-disabled'>Cancelado</a>";
                } else { // Pendente
                    echo "<a class='btn-acao btn-confirmar' href='?acao=confirmar&id={$row['Cod_agendamento']}'>Confirmar</a>";
                    echo "<a class='btn-acao btn-cancelar' href='?acao=cancelar&id={$row['Cod_agendamento']}'>Cancelar</a>";
                }

                echo "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='6' style='text-align:center;'>Nenhum agendamento encontrado.</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>
