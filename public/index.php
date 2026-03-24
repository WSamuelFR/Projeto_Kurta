<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>feel.it - Conecte-se</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Meta tag description para SEO -->
    <meta name="description" content="Acesse o feel.it, sua rede social de emoções.">
</head>
<body>
    <div class="login-container">
        <!-- background accents (blob animado) -->
        <div class="blob-1"></div>
        <div class="blob-2"></div>

        <div class="glass-card">
            <div class="logo-area">
                <h1>feel.it</h1>
                <p>Mergulhe nas suas emoções.</p>
            </div>

            <form id="loginForm" class="login-form">
                <div class="input-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required placeholder="seu@email.com">
                </div>
                <div class="input-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
                
                <div class="recover-link">
                    <a href="#">Esqueceu a senha?</a>
                </div>

                <div id="alertBox" class="alert-box d-none"></div>

                <button type="submit" id="btnAcessar" class="btn-primary">
                    <span class="btn-text">Acessar</span>
                    <span class="btn-loader d-none"></span>
                </button>
            </form>

            <div class="register-area">
                <p>Ainda não tem conta? <a href="view/cadastro.php">Registre-se</a></p>
            </div>
        </div>
    </div>

    <!-- Script de validação e comunicação POST -->
    <script src="assets/js/login.js"></script>
</body>
</html>
