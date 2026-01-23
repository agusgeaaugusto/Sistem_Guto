<?php
if(isset($_FILES['image'])) {
    $targetDir = "uploads/";
    $fileName = basename($_FILES['image']['name']);
    $targetFilePath = $targetDir . $fileName;

    // Verificar se o arquivo é uma imagem válida
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $allowTypes = array('jpg','png','jpeg','gif');
    if(in_array($fileType, $allowTypes)) {
        // Mover o arquivo para o diretório de destino
        if(move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            echo json_encode(['success' => true, 'filePath' => $targetFilePath]);
        } else {
            echo json_encode(['success' => false]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Tipo de arquivo inválido']);
    }
}
?>
