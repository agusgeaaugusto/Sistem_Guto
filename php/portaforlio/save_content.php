<?php
// Conectar ao banco de dados
include 'conexion_bi.php';

// Receber dados do JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// Atualizar a seção "Sobre Mim" no banco de dados
if(isset($data['sobre'])) {
    $stmt = $conn->prepare("UPDATE conteudo SET texto = :sobre WHERE secao = 'sobre'");
    $stmt->execute(['sobre' => $data['sobre']]);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
