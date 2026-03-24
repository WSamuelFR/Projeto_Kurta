document.addEventListener("DOMContentLoaded", () => {
    const btnSino = document.getElementById('btnSinoNotificacao');
    const badgeSino = document.getElementById('badgeSinoAlert');
    const dropDown = document.getElementById('notificationsDropdown');
    const notiList = document.getElementById('notificationsList');

    if (!btnSino || !dropDown) return;

    // Fechar dropdown clicando fora
    document.addEventListener('click', (e) => {
        if (!btnSino.contains(e.target) && !dropDown.contains(e.target)) {
            dropDown.classList.remove('show');
        }
    });

    // Toggle Dropdown e Limpar Badges
    btnSino.addEventListener('click', async () => {
        const isHidden = !dropDown.classList.contains('show');
        if (isHidden) {
            dropDown.classList.add('show');
            // Animação leve
            dropDown.animate([ { opacity: 0, transform: 'translateY(-10px)' }, { opacity: 1, transform: 'translateY(0)' } ], { duration: 200, easing: 'ease-out' });
            
            // Renderiza
            await fetchAndRenderNotifications();

            // Se tem unread, envia sinal para API zerar tudo no banco
            if (badgeSino.style.display !== 'none') {
                try {
                    await fetch('../../app/controller/notification_controller/mark_read.php', { method: 'POST' });
                    badgeSino.style.display = 'none'; // Some badge
                    badgeSino.textContent = '0';
                } catch(e) { console.error('Error marking as read', e); }
            }
        } else {
            dropDown.classList.remove('show');
        }
    });

    const getNotifPhrase = (type) => {
        if(type === 'like') return 'Curtiu o seu post!';
        if(type === 'comment') return 'Comentou na sua postagem!';
        if(type === 'friend') return 'Aceitou seu pedido de amizade!';
        return 'Interagiu com você.';
    }

    const fetchAndRenderNotifications = async () => {
        try {
            const res = await fetch('../../app/controller/notification_controller/load_notifications.php');
            const data = await res.json();
            
            if (data.status === 'success') {
                // Update badge
                if (data.unread_count > 0 && !dropDown.classList.contains('show')) {
                    badgeSino.style.display = 'flex';
                    badgeSino.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                }

                // Render List
                if(data.data.length === 0) {
                    notiList.innerHTML = '<div class="glass-notif empty-state">Nenhuma notificação por enquanto...</div>';
                    return;
                }
                
                let html = '';
                data.data.forEach(n => {
                    // Fallback da foto de perfil igual fizemos nos feeds
                    const fallbackPic = `https://ui-avatars.com/api/?background=0ea5e9&color=fff&name=${encodeURIComponent(n.first_name)}`;
                    let avatarUrl = fallbackPic;
                    if (n.profile_pic) {
                        avatarUrl = n.profile_pic.startsWith('http') ? n.profile_pic : '../' + n.profile_pic;
                    }

                    // Se nao foi lido, adiciona bolinha
                    const readIndicator = n.is_read == 0 ? '<span class="unread-dot"></span>' : '';
                    const notiIcon = n.notif_type === 'like' ? '❤️' : (n.notif_type === 'comment' ? '💬' : '🔔');

                    html += `
                        <div class="glass-notif ${n.is_read == 0 ? 'unread' : ''}">
                            <img src="${avatarUrl}" class="notif-avatar" onerror="this.src='${fallbackPic}'">
                            <div class="notif-body">
                                <strong>${n.first_name} ${n.last_name}</strong> 
                                ${getNotifPhrase(n.notif_type)} <span style="font-size:0.8rem;">${notiIcon}</span>
                                <div class="notif-date">${new Date(n.created_at).toLocaleString('pt-BR')}</div>
                            </div>
                            ${readIndicator}
                        </div>
                    `;
                });
                notiList.innerHTML = html;
            }
        } catch(err) {
            console.error('Erro ao buscar notificacoes', err);
        }
    };

    // Initial Fetch Badge Only
    fetchAndRenderNotifications();
    // Poll every 45 secs pra manter badge viva se tiver aberto o site num monitor
    setInterval(fetchAndRenderNotifications, 45000);
});
