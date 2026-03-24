document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const alertBox = document.getElementById('alertBox');
    const btnAcessar = document.getElementById('btnAcessar');
    const btnText = btnAcessar.querySelector('.btn-text');
    const btnLoader = btnAcessar.querySelector('.btn-loader');

    const showAlert = (message, isError = true) => {
        alertBox.textContent = message;
        alertBox.className = `alert-box d-block ${isError ? 'alert-error' : 'alert-success'}`;
        
        // Micro-anim for error
        if (isError) {
            alertBox.animate([
                { transform: 'translateX(-5px)' },
                { transform: 'translateX(5px)' },
                { transform: 'translateX(-5px)' },
                { transform: 'translateX(0)' }
            ], { duration: 300 });
        }
    };

    const toggleLoading = (isLoading) => {
        if (isLoading) {
            btnText.classList.add('d-none');
            btnLoader.classList.add('d-block');
            btnAcessar.disabled = true;
        } else {
            btnText.classList.remove('d-none');
            btnLoader.classList.remove('d-block');
            btnAcessar.disabled = false;
        }
    };

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        if(!email || !password) {
            showAlert('Por favor, preencha todos os campos.', true);
            return;
        }

        toggleLoading(true);
        alertBox.classList.remove('d-block');

        try {
            // Requisição para o controller
            const response = await fetch('../app/controller/auth_controller/process_login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showAlert(data.message, false);
                // Redirecionamento futuro para dashboard/feed
                setTimeout(() => {
                    window.location.href = 'view/home.php';
                }, 1000);
            } else {
                showAlert(data.message || 'Erro ao realizar login.', true);
            }
        } catch (error) {
            showAlert('Erro de conexão com o servidor. Tente novamente.', true);
            console.error('Login error:', error);
        } finally {
            toggleLoading(false);
        }
    });
});
