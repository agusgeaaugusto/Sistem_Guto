<?php
include 'conexion_bi.php';

// Consultar o conteúdo
$stmt = $conn->query("SELECT texto FROM conteudo WHERE secao = 'sobre'");
$sobre = $stmt->fetch(PDO::FETCH_ASSOC)['texto'];

// Pegar as imagens da galeria
$images = glob("uploads/*.{jpg,png,jpeg,gif}", GLOB_BRACE);

echo json_encode(['sobre' => $sobre, 'images' => $images]);
?>
