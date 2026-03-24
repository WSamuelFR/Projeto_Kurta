document.addEventListener("DOMContentLoaded", () => {
    const clanId = window.CURRENT_CLAN_ID;
    const userRole = window.USER_ROLE;

    const btnJoin = document.getElementById('btnJoinClan');
    const btnMembros = document.getElementById('btnMembrosClan');
    const btnSettings = document.getElementById('btnSettingsClan');
    const modalMembers = document.getElementById('modalClanMembers');
    const modalSettings = document.getElementById('modalClanSettings');
    
    // Join Clan
    if (btnJoin) {
        btnJoin.addEventListener('click', async () => {
            const originalText = btnJoin.innerText;
            btnJoin.innerText = 'Entrando...';
            btnJoin.disabled = true;

            try {
                const res = await fetch('../../app/controller/clan_controller/join_clan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ clan_id: clanId })
                });
                const result = await res.json();
                
                if (result.status === 'success') {
                    window.location.reload();
                } else {
                    if(window.showToast) window.showToast(result.message, true); else alert(result.message);
                    btnJoin.innerText = originalText;
                    btnJoin.disabled = false;
                }
            } catch (err) {
                console.error("Erro ao join", err);
                if(window.showToast) window.showToast("Falha de conexão.", true); else alert("Sem conexão.");
                btnJoin.innerText = originalText;
                btnJoin.disabled = false;
            }
        });
    }

    // Modal Toggles
    const closeModals = () => {
        if(modalMembers) { modalMembers.style.display = 'none'; modalMembers.classList.add('d-none'); }
        if(modalSettings) { modalSettings.style.display = 'none'; modalSettings.classList.add('d-none'); }
    }

    document.querySelectorAll('.btnCloseModalClan').forEach(btn => {
        btn.addEventListener('click', closeModals);
    });

    document.addEventListener('click', (e) => {
        if (e.target === modalMembers || e.target === modalSettings) closeModals();
    });

    // Abrir Modal de Membros e Carregar Dados
    if (btnMembros && modalMembers) {
        btnMembros.addEventListener('click', async () => {
            modalMembers.classList.remove('d-none');
            modalMembers.style.display = 'flex';
            modalMembers.animate([{opacity: 0, transform: 'scale(0.95)'}, {opacity: 1, transform: 'scale(1)'}], {duration: 250, easing: 'ease'});
            
            const listAjax = document.getElementById('membersListAjax');
            listAjax.innerHTML = '<p style="text-align: center; color: rgba(255,255,255,0.5);">Buscando guerreiros...</p>';
            
            try {
                const res = await fetch(`../../app/controller/clan_controller/get_members.php?clan_id=${clanId}`);
                const data = await res.json();
                if(data.status === 'success') {
                    listAjax.innerHTML = data.html;
                    
                    // Bindings do Rei (Mudar Patente)
                    document.querySelectorAll('.acao-patente-cla').forEach(btn => {
                        btn.addEventListener('click', async (e) => {
                            e.preventDefault();
                            const alvoId = btn.getAttribute('data-targetid');
                            const novoCargo = btn.getAttribute('data-cargo');
                            const acaoNome = novoCargo === 'lider' ? 'Promover a Líder' : 'Rebaixar a Aldeão';
                            
                            window.showConfirm(`Tem certeza que deseja ${acaoNome} este combatente?`, async () => {
                                btn.disabled = true;
                                btn.textContent = "Processando...";
                                try {
                                    const roleReq = await fetch('../../app/controller/clan_controller/change_role.php', {
                                        method: 'POST',
                                        headers: {'Content-Type': 'application/json'},
                                        body: JSON.stringify({clan_id: clanId, target_user_id: alvoId, new_role: novoCargo})
                                    });
                                    const roleRes = await roleReq.json();
                                    if(roleRes.status === 'success') {
                                        window.showToast(roleRes.message, false);
                                        btnMembros.click(); // Recarrega Modal Silenciosamente pra atualizar cores/botoes!
                                    } else {
                                        window.showToast(roleRes.message, true);
                                        btn.disabled = false;
                                        btn.textContent = "Tentar Novamente";
                                    }
                                } catch(err) {
                                    window.showToast("Falha de Conexão com a Sala do Trono.", true);
                                    btn.disabled = false;
                                }
                            });
                        });
                    });

                    // Binding Rei (Expulsar Membro)
                    document.querySelectorAll('.acao-expulsar-membro').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            e.preventDefault();
                            const alvoId = btn.getAttribute('data-targetid');
                            
                            window.showConfirm('Deseja realmente aplicar o EXÍLIO absoluto neste membro? Ele será chutado para fora dos domínios do Clã.', async () => {
                                btn.disabled = true;
                                btn.textContent = "Exilando...";
                                try {
                                    const kickReq = await fetch('../../app/controller/clan_controller/remove_member.php', {
                                        method: 'POST',
                                        headers: {'Content-Type': 'application/json'},
                                        body: JSON.stringify({clan_id: clanId, target_user_id: alvoId})
                                    });
                                    const kickRes = await kickReq.json();
                                    if(kickRes.status === 'success') {
                                        window.showToast(kickRes.message, false);
                                        btnMembros.click(); // Reload Visual Subito
                                        // Opcional reload da pagina pro count cair
                                        setTimeout(() => window.location.reload(), 1500);
                                    } else {
                                        window.showToast(kickRes.message, true);
                                        btn.disabled = false;
                                        btn.textContent = "Expulsar";
                                    }
                                } catch(err) {
                                    window.showToast("Falha brutal ao exilar membro.", true);
                                    btn.disabled = false;
                                }
                            });
                        });
                    });

                } else {
                    listAjax.innerHTML = `<p style="color:#ef4444;text-align:center;">Erro: ${data.error || 'Desconhecido'}</p>`;
                }
            } catch(e) {
                listAjax.innerHTML = '<p style="color:#ef4444;text-align:center;">Conexão falhou.</p>';
            }
        });
    }

    // Modal Gerenciar Clã
    if (btnSettings && modalSettings) {
        btnSettings.addEventListener('click', () => {
            modalSettings.classList.remove('d-none');
            modalSettings.style.display = 'flex';
            modalSettings.animate([{opacity: 0, transform: 'scale(0.95)'}, {opacity: 1, transform: 'scale(1)'}], {duration: 250, easing: 'ease'});
        });
    }

    // Atualizar Dados Rei
    const formSettings = document.getElementById('formClanSettings');
    if (formSettings) {
        formSettings.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = formSettings.querySelector('button[type="submit"]');
            btn.textContent = 'Salvando...'; btn.disabled=true;
            
            const formData = new FormData(formSettings);
            const payload = {
                action: 'update',
                clan_id: clanId,
                name: formData.get('name'),
                desc: formData.get('description'),
                vis: formData.get('visibility')
            };

            try {
                const r = await fetch('../../app/controller/clan_controller/update_clan.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                const response = await r.json();
                if(response.status === 'success') {
                    window.location.reload();
                } else {
                    if(window.showToast) window.showToast(response.message, true); else alert(response.message);
                    btn.textContent = '💾 Salvar Alterações'; btn.disabled=false;
                }
            } catch(ex) {
                btn.textContent = '💾 Salvar Alterações'; btn.disabled=false;
            }
        });
    }

    // Excluir Clan
    const btnDelete = document.getElementById('btnDeleteClan');
    if (btnDelete) {
        btnDelete.addEventListener('click', async () => {
            const conf = await (window.showConfirm ? window.showConfirm("Deseja DELETAR este Clã? A ação não tem volta!") : confirm("Deseja DELETAR o Clã para sempre?"));
            if (conf) {
                btnDelete.textContent = "Destruindo..."; btnDelete.disabled=true;
                try {
                    const r = await fetch('../../app/controller/clan_controller/update_clan.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({action: 'delete', clan_id: clanId})
                    });
                    const result = await r.json();
                    if(result.status === 'success') {
                        window.location.href = 'home.php';
                    } else {
                        if(window.showToast) window.showToast(result.message, true); else alert(result.message);
                        btnDelete.textContent = "☠️ Exterminar Clã"; btnDelete.disabled=false;
                    }
                } catch(e) {
                    btnDelete.textContent = "☠️ Exterminar Clã"; btnDelete.disabled=false;
                }
            }
        });
    }

    // ==========================================
    // MÓDULO DE COMUNICADOS E FEED DO CLÃ
    // ==========================================
    
    // Modal Ditar Comunicado
    const btnMasterFeeling = document.getElementById('btnMasterClanFeeling');
    const modalFeeling = document.getElementById('modalClanFeeling');
    const btnCloseFeeling = document.getElementById('btnCloseClanFeeling');
    const txtFeeling = document.getElementById('clanFeelingTextInput');
    const btnSubmitFeeling = document.getElementById('btnSubmitClanFeeling');
    const charCount = document.getElementById('clanCharCount');

    if(btnMasterFeeling && modalFeeling) {
        btnMasterFeeling.addEventListener('click', () => {
            modalFeeling.classList.remove('d-none');
            modalFeeling.style.display = 'flex';
            modalFeeling.animate([{opacity: 0, transform: 'scale(0.95)'}, {opacity: 1, transform: 'scale(1)'}], {duration: 250, easing: 'ease'});
            txtFeeling.value = '';
            charCount.textContent = '0';
            btnSubmitFeeling.textContent = 'Publicar Feeling';
            btnSubmitFeeling.removeAttribute('data-editar-id');
            modalFeeling.querySelector('h3').textContent = 'Expresse um novo Feeling!';
        });
    }

    if(btnCloseFeeling) {
        btnCloseFeeling.addEventListener('click', () => {
            modalFeeling.style.display = 'none';
            modalFeeling.classList.add('d-none');
        });
    }

    if(txtFeeling) {
        txtFeeling.addEventListener('input', () => {
            charCount.textContent = txtFeeling.value.length;
        });
    }

    // Engine: Carregar Feed do Clã
    const feedContainer = document.getElementById('feedContainerWrapper');
    const loadClanFeeds = async () => {
        if (!feedContainer) return;

        try {
            const response = await fetch(`../../app/controller/feeling_controller/load_clan_feelings.php?clan_id=${clanId}`);
            const result = await response.json();

            if (result.status === 'success') {
                if (result.data.length === 0) {
                    feedContainer.innerHTML = `
                        <div class="glass-card feed-box" style="text-align: center; padding: 40px;">
                            <span style="font-size: 2rem; color: rgba(255,255,255,0.5);">🌪️</span>
                            <p class="empty-state" style="margin-top: 15px;">Os ventos estão calmos... Ninguém postou comunicados neste clã ainda.</p>
                        </div>
                    `;
                    return;
                }

                feedContainer.innerHTML = '';
                result.data.forEach(post => {
                    const objData = new Date(post.created_at);
                    const formatData = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'long', timeStyle: 'short' }).format(objData);
                    const fullName = `${post.first_name} ${post.last_name || ''}`.trim();
                    const picture = post.profile_pic || '../assets/files/default_avatar.png';
                    const fallbackPic = `https://ui-avatars.com/api/?background=4f46e5&color=fff&name=${encodeURIComponent(fullName)}`; 

                    // Opções administrativas se Vc for o dono do post, lider ou rei (Vamos basear no dono do post para editar, e excluir p lider/rei)
                    const isDonno = String(post.user_id) === String(window.CURRENT_USER_ID);
                    const contextMenu = (!isDonno && userRole !== 'rei' && userRole !== 'lider') ? '' : `
                        <div class="post-options">
                            <button type="button" class="btn-dots bind-dynamic-dots" title="Opções Administrativas">⋮</button>
                            <ul class="dropdown-menu-post d-none">
                                ${isDonno ? `<li><a href="#" class="drop-item acao-editar" data-id="${post.feeling_id}" data-texto="${encodeURIComponent(post.feeling)}">✏️ Correção Ortográfica (Editar)</a></li>` : ''}
                                <li><a href="#" class="drop-item text-danger acao-apagar" data-id="${post.feeling_id}">🗑️ Remoção Definitiva</a></li>
                            </ul>
                        </div>
                    `;

                    // Template Exato do Perfil para reuso 100% de CSS e JS
                    const htmlCard = `
                    <div class="feed-post-card glass-card">
                        <div class="post-header">
                            <div class="post-author-info">
                                <img src="${picture}" alt="Avatar Autor" class="post-avatar" onerror="this.src='${fallbackPic}'">
                                <strong class="post-author-name">${fullName}</strong>
                            </div>
                            ${contextMenu}
                        </div>
                        <div class="post-body">
                            <p class="post-text">${post.feeling}</p>
                            <span class="post-date" style="display:flex; align-items:center; gap:8px;">
                                ${formatData}
                            </span>
                        </div>
                        <div class="post-footer">
                            <button class="btn-action acao-abrir-comments" data-id="${post.feeling_id}" style="color: var(--text-muted);"><span style="font-size:1.2rem;">💬</span> <span id="master-comments-count-${post.feeling_id}">${post.comments_count || 0}</span> Comentários</button>
                            <button type="button" class="btn-action btn-like" data-id="${post.feeling_id}" style="color: ${post.user_has_liked > 0 ? '#ef4444' : 'var(--text-muted)'};">
                                <span class="icon">${post.user_has_liked > 0 ? '❤️' : '🤍'}</span> 
                                <span class="like-count" id="like-count-${post.feeling_id}">${post.likes_count}</span> Curtidas
                            </button>
                        </div>

                        <div class="comments-section d-none" id="comments-section-${post.feeling_id}">
                            <div class="comments-list" id="comments-list-${post.feeling_id}"></div>
                            <div class="comment-input-area">
                                <div id="action-badge-${post.feeling_id}" class="d-none" style="font-size:0.8rem; color:var(--neon-text); margin-bottom:-5px;">
                                    <span id="action-text-${post.feeling_id}">Ação</span>
                                    <a href="#" class="cancel-action" data-id="${post.feeling_id}">✕ Cancelar</a>
                                </div>
                                <div class="comment-textarea-wrapper">
                                    <textarea class="comment-textarea" id="comment-input-${post.feeling_id}" placeholder="Registrar pensamento interativo... (Máx 1500 chars)" maxlength="1500" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                                    <button class="comment-submit-btn acao-enviar-comment" data-id="${post.feeling_id}">Sincronizar</button>
                                </div>
                            </div>
                        </div>
                    </div>`;
                    
                    feedContainer.innerHTML += htmlCard;
                });

                // ATIVAÇÃO DOS DROPDOWNS e ACTIONS
                document.querySelectorAll('.bind-dynamic-dots').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault(); e.stopPropagation(); 
                        const menuDropdown = btn.nextElementSibling;
                        document.querySelectorAll('.dropdown-menu-post').forEach(menu => {
                            if(menu !== menuDropdown) menu.classList.add('d-none');
                        });
                        menuDropdown.classList.toggle('d-none');
                    });
                });

                document.querySelectorAll('.acao-apagar').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.preventDefault(); e.stopPropagation();
                        window.showConfirm('🚨 ALERTA: Tem certeza que deseja excluir esta postagem? A ação no banco de dados é mortal e não poderá ser desfeita!', async () => {
                            const idDoPost = btn.getAttribute('data-id');
                            try {
                                const res = await fetch('../../app/controller/feeling_controller/delete_feeling.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ feeling_id: idDoPost })
                                });
                                const data = await res.json();
                                if(data.status === 'success') {
                                    loadClanFeeds(); 
                                    window.showToast('Postagem incinerada!', false);
                                } else { window.showToast(data.message, true); }
                            } catch(err) { console.error(err); }
                        }); 
                    });
                });

                document.querySelectorAll('.acao-editar').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault(); e.stopPropagation();
                        const idDoPost = btn.getAttribute('data-id');
                        const oldRawText = decodeURIComponent(btn.getAttribute('data-texto'));
                        
                        modalFeeling.querySelector('h3').textContent = "✏️ Reescreva as batidas do seu Feeling";
                        txtFeeling.value = oldRawText; 
                        charCount.textContent = txtFeeling.value.length;
                        
                        btnSubmitFeeling.setAttribute('data-editar-id', idDoPost);
                        btnSubmitFeeling.textContent = "Atualizar Relato Pessoal";

                        modalFeeling.classList.remove('d-none');
                        modalFeeling.style.display = 'flex';
                    });
                });

                // --- NOVO: Lógica de Curtidas (Like Toggle) ---
                document.querySelectorAll('.btn-like').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.preventDefault(); e.stopPropagation();
                        const feelingId = btn.getAttribute('data-id');
                        try {
                            const res = await fetch('../../app/controller/feeling_controller/toggle_like.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ feeling_id: feelingId })
                            });
                            const data = await res.json();
                            if (data.status === 'success') {
                                const counter = document.getElementById(`like-count-${feelingId}`);
                                if(counter) counter.textContent = data.new_count;
                                const icon = btn.querySelector('.icon');
                                if (data.action === 'liked') {
                                    btn.style.color = '#ef4444';
                                    if(icon) icon.textContent = '❤️';
                                } else {
                                    btn.style.color = 'var(--text-muted)';
                                    if(icon) icon.textContent = '🤍';
                                }
                            }
                        } catch(err) { console.error('Error toggling like', err); }
                    });
                });

            } else {
                feedContainer.innerHTML = `<div class="glass-card feed-box"><p class="empty-state text-danger">Erro de Carga DB MVC: ${result.message}</p></div>`;
            }
        } catch (err) {
            console.error("API Frontend Load Error", err);
            feedContainer.innerHTML = `<div class="glass-card feed-box"><p class="empty-state text-danger">Erro de comunicação com o servidor.</p></div>`;
        }
    };
    
    // Iniciar Módulo Feeds Se A Área Existir (Verificado no HTML clan.php)
    if(feedContainer) {
        loadClanFeeds();
    }

    // Engine Mestre de Criação/Edicao Puxando Request Rest JSON
    if(btnSubmitFeeling) {
        btnSubmitFeeling.addEventListener('click', async (e) => {
            e.preventDefault();
            const texto = txtFeeling.value.trim();
            if(!texto) {
                window.showToast('O seu feeling não pode estar vazio!', true); return;
            }

            const isEdit = btnSubmitFeeling.hasAttribute('data-editar-id');
            const editId = btnSubmitFeeling.getAttribute('data-editar-id');
            
            const reqData = isEdit 
                ? { feeling_id: editId, body: texto }
                : { body: texto, publish_target: 'cla', cla_id: clanId, public_visibility: 'public' };
                
            const endpoint = isEdit ? '../../app/controller/feeling_controller/edit_feeling.php' : '../../app/controller/feeling_controller/feeling_process.php';

            const originalBtnText = btnSubmitFeeling.textContent;
            btnSubmitFeeling.textContent = 'Trabalhando no Banco SQL...';
            btnSubmitFeeling.disabled = true;

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(reqData)
                });
                const data = await response.json();

                if(data.status === 'success') {
                    window.showToast(data.message, false);
                    modalFeeling.style.display = 'none';
                    modalFeeling.classList.add('d-none');
                    txtFeeling.value = '';
                    loadClanFeeds();
                } else {
                    window.showToast(data.message, true);
                }
            } catch(e) {
                window.showToast('Falha na API Controller.', true);
            } finally {
                btnSubmitFeeling.textContent = originalBtnText;
                btnSubmitFeeling.disabled = false;
            }
        });
    }

    // Ouvinte Absoluto Body para Fechar Dropdowns Drop-menus Clique Fora
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.post-options') && !e.target.closest('.dropdown-menu-post')) {
            document.querySelectorAll('.dropdown-menu-post').forEach(menu => {
                menu.classList.add('d-none');
            });
        }
        if (e.target === modalFeeling && modalFeeling) {
            modalFeeling.style.display = 'none';
            modalFeeling.classList.add('d-none');
        }
    });

});
