document.addEventListener('DOMContentLoaded', () => {
    const cadastroForm = document.getElementById('cadastroForm');
    const alertBox = document.getElementById('alertBox');
    const btnRegister = document.getElementById('btnRegister');
    const btnText = btnRegister.querySelector('.btn-text');
    const btnLoader = btnRegister.querySelector('.btn-loader');

    const showAlert = (message, isError = true) => {
        alertBox.textContent = message;
        alertBox.className = `alert-box d-block ${isError ? 'alert-error' : 'alert-success'}`;
        
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
            btnRegister.disabled = true;
        } else {
            btnText.classList.remove('d-none');
            btnLoader.classList.remove('d-block');
            btnRegister.disabled = false;
        }
    };

    cadastroForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const first_name = document.getElementById('firstName').value.trim();
        const last_name = document.getElementById('lastName').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();

        // Validação no frontend previne envios viciosos
        if(!first_name || !email || !password) {
            showAlert('Os campos Nome, E-mail e Senha são de preenchimento obrigatório.', true);
            return;
        }

        if (password.length < 5) {
            showAlert('Por segurança, a sua senha deve conter ao menos 5 caracteres.', true);
            return;
        }

        toggleLoading(true);
        alertBox.classList.remove('d-block');

        try {
            // Requisição para o controller
            const response = await fetch('../../app/controller/auth_controller/cadastro_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ first_name, last_name, phone, email, password })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showAlert(data.message, false);
                // Após o sucesso do cadastro o sistema redireciona a index para login.
                setTimeout(() => {
                    window.location.href = '../index.php';
                }, 2000);
            } else {
                showAlert(data.message || 'Houve um erro indesejado na inserção do cadastro.', true);
            }
        } catch (error) {
            showAlert('Problemas na conexão com o servidor detectados. Tente reiniciar e tentar novamente.', true);
            console.error('Registration fetch error:', error);
        } finally {
            toggleLoading(false);
        }
    });
});
