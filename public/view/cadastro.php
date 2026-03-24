<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>feel.it - Crie sua conta</title>
    <!-- Fonts e CSS Base -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <!-- Background accents -->
        <div class="blob-1"></div>
        <div class="blob-2"></div>

        <div class="glass-card">
            <div class="logo-area" style="margin-bottom: 2rem;">
                <h1>feel.it</h1>
                <p>Crie sua nova conta abaixo</p>
            </div>

            <form id="cadastroForm" class="login-form">
                
                <div class="input-group" style="display: flex; gap: 10px; margin-bottom: 1.2rem;">
                    <div style="flex: 1;">
                        <label for="firstName">Primeiro Nome</label>
                        <input type="text" id="firstName" name="first_name" required placeholder="Ex: Lucas">
                    </div>
                    <div style="flex: 1;">
                        <label for="lastName">Sobrenome</label>
                        <input type="text" id="lastName" name="last_name" placeholder="Ex: Silva">
                    </div>
                </div>

                <div class="input-group" style="margin-bottom: 1.2rem;">
                    <label for="phone">Telefone</label>
                    <input type="tel" id="phone" name="phone" placeholder="(11) 90000-0000">
                </div>

                <div class="input-group" style="margin-bottom: 1.2rem;">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required placeholder="seu@email.com">
                </div>

                <div class="input-group" style="margin-bottom: 1.8rem;">
                    <label for="password">Crie uma Senha</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>

                <div id="alertBox" class="alert-box d-none"></div>

                <button type="submit" id="btnRegister" class="btn-primary">
                    <span class="btn-text">Inscrever-se</span>
                    <span class="btn-loader d-none"></span>
                </button>
            </form>

            <div class="register-area">
                <p>Já possui uma conta? <a href="../index.php">Faça Login</a></p>
            </div>
        </div>
    </div>

    <!-- Script de submissão do form -->
    <script src="../assets/js/cadastro.js"></script>
</body>
</html>
