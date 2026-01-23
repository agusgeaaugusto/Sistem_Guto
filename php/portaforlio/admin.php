<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin - Editar Portfólio de [Seu Nome]">
    <title>Admin - Editar Portfólio</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f4f4;
        }
        header {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        section {
            padding: 20px;
            margin: 20px;
        }
        .editable {
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }
        .gallery img {
            width: 100%;
            height: auto;
        }
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>

<header>
    <h1>Admin - Editar Portfólio de [Seu Nome]</h1>
</header>

<section>
    <h2>Sobre Mim</h2>
    <div contenteditable="true" id="sobre" class="editable">
        Olá! Sou [Seu Nome], desenvolvedor web apaixonado por criar soluções inovadoras.
    </div>

    <h2>Galeria de Fotos</h2>
    <form id="uploadForm" enctype="multipart/form-data">
        <input type="file" name="image" id="imageUpload">
        <button type="submit">Upload Imagem</button>
    </form>
    <div class="gallery" id="gallery">
        <!-- As imagens serão carregadas aqui -->
    </div>
</section>

<footer>
    <button id="saveChanges">Salvar Alterações</button>
    <p>&copy; 2024 [Seu Nome] - Todos os direitos reservados.</p>
</footer>

<script>
    document.getElementById('saveChanges').addEventListener('click', function() {
        const sobreText = document.getElementById('sobre').innerText;

        // Fazer uma requisição Ajax para salvar o texto no servidor
        fetch('save_content.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ sobre: sobreText })
        }).then(response => response.json()).then(data => {
            if(data.success) {
                alert('Conteúdo salvo com sucesso!');
            } else {
                alert('Erro ao salvar conteúdo.');
            }
        });
    });

    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('image', document.getElementById('imageUpload').files[0]);

        fetch('upload_image.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json()).then(data => {
            if(data.success) {
                const img = document.createElement('img');
                img.src = data.filePath;
                document.getElementById('gallery').appendChild(img);
            } else {
                alert('Erro ao fazer upload da imagem.');
            }
        });
    });
</script>

</body>
</html>
