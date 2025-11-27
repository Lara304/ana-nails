<?php
$conn = new mysqli("localhost","root","","ananails");
if ($conn->connect_error) die("Conexão falhou: " . $conn->connect_error);

if(isset($_POST['cadastrar'])) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO ADMIN (nome, email, senha) VALUES ('$nome', '$email', '$senha')";
    if($conn->query($sql) === TRUE){
        header("Location: login_adm.php");
        exit();
    } else {
        $erro = "Erro: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Cadastro Administrador</title>
<link rel="stylesheet" href="style_adm.css">
</head>
<body>

<header>Área do Administrador</header>

<div class="container">
    <h2>Cadastro</h2>

    <?php if(isset($erro)) echo "<p class='error'>$erro</p>"; ?>

    <form method="POST">
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit" name="cadastrar">Cadastrar</button>
    </form>

    <a href="login_adm.php">Já tem conta? Fazer login</a>
</div>

</body>
</html>
