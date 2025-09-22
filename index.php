<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Estado de la API</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      margin-top: 50px;
    }
    #status {
      font-size: 24px;
      margin-top: 20px;
    }
    .up {
      color: green;
    }
    .down {
      color: red;
    }
  </style>
</head>
<body>
  <h1>Verificación de API</h1>
  <p id="status">⏳ Verificando...</p>

  <script>
    // Cambia esta URL por tu endpoint real
    const apiUrl = 'https://tuservidor.com/tuapi';

    fetch(apiUrl)
      .then(response => {
        if (response.ok) {
          document.getElementById('status').textContent = '✅ La API está funcionando';
          document.getElementById('status').className = 'up';
        } else {
          document.getElementById('status').textContent = '❌ La API NO está disponible';
          document.getElementById('status').className = 'down';
        }
      })
      .catch(() => {
        document.getElementById('status').textContent = '❌ La API NO está disponible';
        document.getElementById('status').className = 'down';
      });
  </script>
</body>
</html>
