document.addEventListener("DOMContentLoaded", () => {
    const btnOpen = document.getElementById('btnCreateClanModal');
    const btnClose = document.getElementById('btnCloseClanModal');
    const modal = document.getElementById('modalCreateClan');
    const form = document.getElementById('formCreateClan');

    if (btnOpen && modal) {
        btnOpen.addEventListener('click', () => {
            modal.classList.remove('d-none');
            modal.style.display = 'flex'; // override if hidden
            // anim
            modal.animate([{opacity: 0}, {opacity: 1}], {duration: 250, easing: 'ease'});
        });
    }

    const fecharModal = () => {
        if(modal) {
            modal.style.display = 'none';
            modal.classList.add('d-none');
            form.reset(); // clear inputs
        }
    };

    if (btnClose) btnClose.addEventListener('click', fecharModal);

    if (modal) {
        // close clicking outside card
        modal.addEventListener('click', (e) => {
            if (e.target === modal) fecharModal();
        });
    }

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);

            // Change button to loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Fundando...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('../../app/controller/clan_controller/create_clan.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    // Redirect to the new clan page directly
                    window.location.href = `clan.php?id=${result.clan_id}`;
                } else {
                    alert('Erro: ' + (result.message || 'Desconhecido'));
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            } catch (err) {
                console.error("Erro ao criar cla:", err);
                alert('Erro na requisição. Verifique sua conexão.');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
    }
});
