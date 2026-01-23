<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portfólio de [Seu Nome]">
    <title>Portfólio - [Seu Nome]</title>
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
    <h1>Portfólio de [Seu Nome]</h1>
</header>

<section>
    <h2>Sobre Mim</h2>
    <div id="sobre">
        <!-- O conteúdo será carregado aqui do banco de dados -->
    </div>

    <h2>Galeria de Fotos</h2>
    <div class="gallery" id="gallery">
        <!-- As imagens serão carregadas aqui -->
    </div>
</section>

<footer>
    <p>&copy; 2024 [Seu Nome] - Todos os direitos reservados.</p>
</footer>

<script>
    // Carregar conteúdo via AJAX
    fetch('get_content.php')
    .then(response => response.json())
    .then(data => {
        document.getElementById('sobre').innerText = data.sobre;
        data.images.forEach(function(image) {
            const img = document.createElement('img');
            img.src = image;
            document.getElementById('gallery').appendChild(img);
        });
    });
</script>

</body>
</html>
