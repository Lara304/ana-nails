<?php
session_start();
$conn = new mysqli("localhost","root","","ananails");
if ($conn->connect_error) die("Conexão falhou: " . $conn->connect_error);

if(isset($_POST['entrar'])){
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM ADMIN WHERE email='$email'";
    $result = $conn->query($sql);

    if($result->num_rows > 0){
        $admin = $result->fetch_assoc();
        if(password_verify($senha, $admin['senha'])){
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_nome'] = $admin['nome'];
            header("Location: agenda_adm.php");
            exit();
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "Administrador não encontrado!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Login Administrador</title>
<link rel="stylesheet" href="style_adm.css">
</head>
<body>

<header>Área do Administrador</header>

<div class="container">
    <h2>Login</h2>
    <?php if(isset($erro)) echo "<p class='error'>$erro</p>"; ?>
    
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit" name="entrar">Entrar</button>
    </form>
    
    <a href="cadastro_adm.php">Criar conta de Administrador</a>
</div>

</body>
</html>
