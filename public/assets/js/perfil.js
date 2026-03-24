document.addEventListener('DOMContentLoaded', () => {

    // ==== ENGINE ASSÍNCRONA DE RENDERIZAÇÃO DO FEED DIV (API MVC GET) ====
    const feedContainer = document.getElementById('feedContainerWrapper');
    const loadReativeFeeds = async () => {
        if (!feedContainer) return;

        try {
            const urlParams = new URLSearchParams(window.location.search);
            const visitorId = urlParams.get('id') ? `?user_id=${urlParams.get('id')}` : '';
            const response = await fetch(`../../app/controller/feeling_controller/load_feelings.php${visitorId}`);
            const result = await response.json();

            if (result.status === 'success') {
                if (result.data.length === 0) {
                    feedContainer.innerHTML = `
                        <div class="glass-card feed-box">
                            <p class="empty-state">Nenhum feeling de sua autoria detectado. O seu diário está em branco! Expresse algo logo acima!</p>
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

                    const contextMenu = (typeof IS_VISITOR !== 'undefined' && IS_VISITOR) ? '' : `
                        <div class="post-options">
                            <button type="button" class="btn-dots bind-dynamic-dots" title="Opções Administrativas">⋮</button>
                            <ul class="dropdown-menu-post d-none">
                                <li><a href="#" class="drop-item acao-privar" data-id="${post.feeling_id}">🔒 Fechadura Rápida Toggle (${post.visibility})</a></li>
                                <li><a href="#" class="drop-item acao-editar" data-id="${post.feeling_id}" data-texto="${encodeURIComponent(post.feeling)}">✏️ Correção Ortográfica (Editar)</a></li>
                                <li><a href="#" class="drop-item text-danger acao-apagar" data-id="${post.feeling_id}">🗑️ Remoção Definitiva</a></li>
                            </ul>
                        </div>
                    `;

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
                                ${post.visibility === 'private' ? '<span style="background:rgba(239, 68, 68, 0.2); color:#ef4444; padding:2px 6px; border-radius:4px; font-weight:bold; font-size:0.75rem;">🔒 Privado</span>' : ''}
                            </span>
                        </div>
                        <div class="post-footer">
                            <button class="btn-action acao-abrir-comments" data-id="${post.feeling_id}" style="color: var(--text-muted);"><span style="font-size:1.2rem;">💬</span> <span id="master-comments-count-${post.feeling_id}">${post.comments_count || 0}</span> Comentários</button>
                            <button type="button" class="btn-action btn-like" data-id="${post.feeling_id}" style="color: ${post.user_has_liked > 0 ? '#ef4444' : 'var(--text-muted)'};">
                                <span class="icon">${post.user_has_liked > 0 ? '❤️' : '🤍'}</span> 
                                <span class="like-count" id="like-count-${post.feeling_id}">${post.likes_count}</span> Curtidas
                            </button>
                            <button type="button" class="btn-action" title="${post.cla_id ? 'Isolado em ID Clã' : 'Feed Global Share'}"><span class="icon">🔁</span> Share</button>
                        </div>
                        
                        <div class="comments-section d-none" id="comments-section-${post.feeling_id}">
                            <div class="comments-list" id="comments-list-${post.feeling_id}"></div>
                            <div class="comment-input-area">
                                <div id="action-badge-${post.feeling_id}" class="d-none" style="font-size:0.8rem; color:var(--neon-text); margin-bottom:-5px;">
                                    <span id="action-text-${post.feeling_id}">Ação</span>
                                    <a href="#" class="cancel-action" data-id="${post.feeling_id}">✕ Cancelar</a>
                                </div>
                                <div class="comment-textarea-wrapper">
                                    <textarea class="comment-textarea" id="comment-input-${post.feeling_id}" placeholder="Escreva seu comentário... (Máx 1500 chars)" maxlength="1500" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                                    <button class="comment-submit-btn acao-enviar-comment" data-id="${post.feeling_id}">Publicar</button>
                                </div>
                            </div>
                        </div>
                    </div>`;
                    
                    feedContainer.innerHTML += htmlCard;
                });

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
                        showConfirm('🚨 ALERTA: Tem certeza que deseja excluir esta postagem? A ação no banco de dados é mortal e não poderá ser desfeita!', async () => {
                        const idDoPost = btn.getAttribute('data-id');
                        try {
                            const res = await fetch('../../app/controller/feeling_controller/delete_feeling.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ feeling_id: idDoPost })
                            });
                            const data = await res.json();
                            if(data.status === 'success') {
                                loadReativeFeeds(); 
                                showToast('Postagem incinerada!', false);
                            } else { showToast(data.message, true); }
                        } catch(err) { console.error(err); }
                        }); 
                    });
                });

                document.querySelectorAll('.acao-privar').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.preventDefault(); e.stopPropagation();
                        const idDoPost = btn.getAttribute('data-id');
                        try {
                            const res = await fetch('../../app/controller/feeling_controller/privacy_feeling.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ feeling_id: idDoPost })
                            });
                            const data = await res.json();
                            if(data.status === 'success') { 
                                showToast('Status de privacidade alterado.', false);
                                loadReativeFeeds(); 
                            }
                            else showToast(data.message, true);
                        } catch(err) { console.error(err); showToast('Erro ao alterar privacidade.', true); }
                    });
                });

                document.querySelectorAll('.acao-editar').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault(); e.stopPropagation();
                        const idDoPost = btn.getAttribute('data-id');
                        const oldRawText = decodeURIComponent(btn.getAttribute('data-texto'));
                        
                        const txtFeeling = document.getElementById('feelingTextInput');
                        const modalNovoFeeling = document.getElementById('modalNovoFeeling');
                        const tituloModal = modalNovoFeeling.querySelector('h3');
                        
                        tituloModal.textContent = "✏️ Reescreva as batidas do seu Feeling";
                        txtFeeling.value = oldRawText; 
                        document.getElementById('charCount').textContent = txtFeeling.value.length;
                        
                        document.querySelector('.privacy-settings').classList.add('d-none');
                        
                        const btnSubm = document.getElementById('btnSubmitFeeling');
                        btnSubm.setAttribute('data-modo', 'editar');
                        btnSubm.setAttribute('data-id-alvo', idDoPost);
                        btnSubm.textContent = "Atualizar Relato Pessoal";

                        modalNovoFeeling.classList.remove('d-none');
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
            feedContainer.innerHTML = `<div class="glass-card feed-box"><p class="empty-state text-danger">Erro de comunicação com o servidor JSON/Fetch.</p></div>`;
        }
    };
    
    loadReativeFeeds();

    // ---- Navegação de Abas Clássicas Dinâmica ----
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');
    
    const modalAddFriend = document.getElementById('modalAddFriend');
    const btnCloseModalAmigo = document.getElementById('btnCloseModalAmigo');
    
    const modalNovoFeeling = document.getElementById('modalNovoFeeling');
    const btnCloseFeeling = document.getElementById('btnCloseFeeling');

    const btnMasterAction = document.getElementById('btnMasterAction');
    const btnMasterText = document.getElementById('btnMasterText');
    let tabAtual = 'tab-feelings'; 
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            
            tabAtual = tab.getAttribute('data-target');
            document.getElementById(tabAtual).classList.add('active');
            
            if (tabAtual === 'tab-feelings') {
                btnMasterText.textContent = '📝 novo feeling!';
            } else if (tabAtual === 'tab-cla') {
                btnMasterText.textContent = '🛡️ criar clã';
            } else if (tabAtual === 'tab-amigos') {
                btnMasterText.textContent = '👤 adicionar novo amigo';
            }
        });
    });

    const prepararModalParaCriarLimpo = () => {
        const txtF = document.getElementById('feelingTextInput');
        const mNF = document.getElementById('modalNovoFeeling');
        const bSub = document.getElementById('btnSubmitFeeling');
        if(!mNF) return;

        txtF.value = '';
        document.getElementById('charCount').textContent = "0";
        mNF.querySelector('h3').textContent = "Expresse um novo Feeling!";
        document.querySelector('.privacy-settings').classList.remove('d-none'); 
        
        bSub.removeAttribute('data-modo'); 
        txtF.value = "";
        document.getElementById('charCount').textContent = "0";
        bSub.textContent = "Publicar Novo Feeling";
        bSub.removeAttribute('data-modo');
        
        mNF.classList.remove('d-none');
    };

    if(btnMasterAction) {
        btnMasterAction.addEventListener('click', (e) => {
            e.preventDefault(); 
            if (tabAtual === 'tab-feelings') {
                prepararModalParaCriarLimpo();
            } else if (tabAtual === 'tab-cla') {
                const modalClass = document.getElementById('modalCreateClan');
                if (modalClass) {
                    modalClass.classList.remove('d-none');
                    modalClass.style.display = 'flex';
                    modalClass.animate([{opacity: 0, transform: 'scale(0.95)'}, {opacity: 1, transform: 'scale(1)'}], {duration: 300, easing: 'ease-out'});
                }
            } else if (tabAtual === 'tab-amigos') {
                modalAddFriend.classList.remove('d-none');
            }
        });
    }

    const fecharModais = () => {
        if(modalAddFriend) modalAddFriend.classList.add('d-none');
        if(modalNovoFeeling) modalNovoFeeling.classList.add('d-none');
        const modalNotif = document.getElementById('modalNotifications');
        if(modalNotif) modalNotif.classList.add('d-none');
        const modalClan = document.getElementById('modalCreateClan');
        if(modalClan) {
            modalClan.classList.add('d-none');
            modalClan.style.display = 'none';
        }
    };

    if (btnCloseModalAmigo) btnCloseModalAmigo.addEventListener('click', fecharModais);
    if (btnCloseFeeling) btnCloseFeeling.addEventListener('click', fecharModais);

    document.addEventListener('click', (e) => {
        const modalNotif = document.getElementById('modalNotifications');
        const modalClan = document.getElementById('modalCreateClan');
        if (e.target === modalAddFriend || e.target === modalNovoFeeling || e.target === modalNotif || e.target === modalClan) {
            fecharModais();
        }
    });

    const radioTargetCla = document.getElementById('radioTargetCla');
    const radioTargetTodos = document.getElementById('radioTargetTodos');
    const wrapperSelectCla = document.getElementById('wrapperSelectCla');
    const wrapperSelectTodos = document.getElementById('wrapperSelectTodos');

    if(radioTargetCla && radioTargetTodos) {
        const alternarPrivacidade = () => {
            if (radioTargetCla.checked) {
                // Selecionou o Clã Revela Select
                wrapperSelectCla.classList.remove('d-none');
                wrapperSelectTodos.classList.add('d-none');
            } else {
                // Reverte Para Todos
                wrapperSelectCla.classList.add('d-none');
                wrapperSelectTodos.classList.remove('d-none');
            }
        };
        radioTargetCla.addEventListener('change', alternarPrivacidade);
        radioTargetTodos.addEventListener('change', alternarPrivacidade);
    }

    const txtFeeling = document.getElementById('feelingTextInput');
    const charCount = document.getElementById('charCount');
    if(txtFeeling) {
        txtFeeling.addEventListener('input', () => {
            charCount.textContent = txtFeeling.value.length;
        });
    }

    const btnDots = document.querySelectorAll('.btn-dots');
    btnDots.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault(); 
            e.stopPropagation(); 
            const menuDropdown = btn.nextElementSibling;
            document.querySelectorAll('.dropdown-menu-post').forEach(menu => {
                if(menu !== menuDropdown) menu.classList.add('d-none');
            });
            menuDropdown.classList.toggle('d-none');
        });
    });

    document.querySelectorAll('.dropdown-menu-post').forEach(menu => {
        menu.addEventListener('click', (e) => {
            e.stopPropagation(); 
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu-post').forEach(menu => {
            menu.classList.add('d-none');
        });
    });

    const btnSubmitFeeling = document.getElementById('btnSubmitFeeling');
    if(btnSubmitFeeling) {
        btnSubmitFeeling.addEventListener('click', async (e) => {
            e.preventDefault();
            const textoOriginal = txtFeeling.value.trim();
            if (!textoOriginal) {
                showToast('Aviso de Segurança: O seu feeling não pode estar vazio.', true);
                btnSubmitFeeling.disabled = false;
                return;
            }

            const modoAtual = btnSubmitFeeling.getAttribute('data-modo') || 'criar';
            let requestData = {};
            let endpointRest = '../../app/controller/feeling_controller/feeling_process.php'; 

            if (modoAtual === 'editar') {
                endpointRest = '../../app/controller/feeling_controller/edit_feeling.php'; 
                requestData = {
                    feeling_id: btnSubmitFeeling.getAttribute('data-id-alvo'),
                    body: textoOriginal
                };
            } else {
                let payloadTarget = 'todos';
                if (document.getElementById('radioTargetCla').checked) { payloadTarget = 'cla'; }
                let payloadClaId = document.getElementById('selectClaOptions').value; 
                let payloadVisibilidade = 'public'; 
                const radiosVisibilidade = document.getElementsByName('public_visibility');
                for(const r of radiosVisibilidade) {
                    if(r.checked) { payloadVisibilidade = r.value; break; }
                }
                requestData = {
                    body: textoOriginal,
                    publish_target: payloadTarget,
                    cla_id: payloadClaId,
                    public_visibility: payloadVisibilidade
                };
            }

            const originalBtnText = btnSubmitFeeling.textContent;
            btnSubmitFeeling.textContent = 'Trabalhando no Banco SQL...';
            btnSubmitFeeling.disabled = true;

            try {
                const response = await fetch(endpointRest, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(requestData)
                });
                
                const data = await response.json();

                if (data.status === 'success') {
                    showToast(data.message, false);
                    fecharModais();
                    txtFeeling.value = '';
                    charCount.textContent = "0";
                    loadReativeFeeds();
                } else {
                    showToast('Erro reportado: ' + data.message, true);
                }
            } catch (error) {
                console.error("Fetch API Error: ", error);
                showToast('Houve um problema de requisição assíncrona com servidor de pastas Controller.', true);
            } finally {
                btnSubmitFeeling.textContent = originalBtnText;
                btnSubmitFeeling.disabled = false;
            }
        });
    }

    // ---- Filtros do Clã Abas (Pills) ----
    const pills = document.querySelectorAll('.btn-pill');
    
    // Função Carregadora Assíncrona dos Clãs pela API
    const loadClans = async (filtroStr) => {
        const tbody = document.getElementById('clanTableBody');
        if(!tbody) return;
        
        tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; color: rgba(255,255,255,0.5); padding: 20px;">Filtrando Dimensões...</td></tr>';
        try {
            const res = await fetch(`../../app/controller/clan_controller/load_clans.php?filter=${filtroStr}`);
            const json = await res.json();
            if(json.status === 'success') {
                tbody.innerHTML = json.html;
            } else {
                tbody.innerHTML = `<tr><td colspan="3" style="text-align: center; color: #ef4444; padding: 20px;">Erro: ${json.message}</td></tr>`;
            }
        } catch(e) {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; color: #ef4444; padding: 20px;">Falha de Conexão Espacial.</td></tr>';
        }
    };

    pills.forEach(pill => {
        pill.addEventListener('click', (e) => {
            e.preventDefault();
            pills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            
            const filtroCorreto = pill.getAttribute('data-filter');
            loadClans(filtroCorreto);
        });
    });

    // Auto-carregar na primeira renderização nativa (Filtro base 'meus-clas')
    if (document.getElementById('clanTableBody')) {
        loadClans('meus-clas');
    }

    // ==========================================
    // MÓDULO 7: AMIZADES E NOTIFICAÇÕES (SPA)
    // ==========================================

    const modalNotifications = document.getElementById('modalNotifications');
    const btnCloseNotifications = document.getElementById('btnCloseNotifications');
    const badgeSino = document.getElementById('badgeSinoAlert');

    const carregarNotificacoes = async () => {
        try {
            const res = await fetch('../../app/controller/perfil_controller/get_notifications.php');
            const result = await res.json();
            if(result.status === 'success') {
                const list = document.getElementById('notificationsListContent');
                list.innerHTML = '';
                
                if (result.data.length > 0) {
                    badgeSino.style.display = 'flex';
                    badgeSino.textContent = result.data.length;
                } else {
                    list.innerHTML = '<p class="text-muted text-center" style="padding:2rem;">Você não possui nenhum convite de amizade pendente.</p>';
                    return;
                }

                result.data.forEach(inv => {
                    const fallbackPic = `https://ui-avatars.com/api/?background=4f46e5&color=fff&name=${encodeURIComponent(inv.first_name + ' ' + (inv.last_name||''))}`;
                    const html = `
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid rgba(255,255,255,0.1);">
                        <div style="display:flex; align-items:center; gap:15px;">
                            <img src="${inv.profile_pic || '../assets/files/default_avatar.png'}" style="width:45px; height:45px; border-radius:50%; object-fit:cover;" onerror="this.src='${fallbackPic}'">
                            <div>
                                <strong style="color:var(--text-main);">${inv.first_name} ${inv.last_name || ''}</strong>
                                <div style="font-size:0.8rem; color:var(--text-muted);">Enviou um pedido de conexão!</div>
                            </div>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <button class="btn-action acao-responder" data-id="${inv.friendship_id}" data-action="accepted" style="color:#10b981; border:1px solid #10b981; padding:0.3rem 0.6rem; border-radius:8px;">✅ Aceitar</button>
                            <button class="btn-action acao-responder" data-id="${inv.friendship_id}" data-action="rejected" style="color:#ef4444; border:1px solid #ef4444; padding:0.3rem 0.6rem; border-radius:8px;">❌ Recusar</button>
                        </div>
                    </div>`;
                    list.innerHTML += html;
                });

                document.querySelectorAll('.acao-responder').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.preventDefault();
                        const fId = btn.getAttribute('data-id');
                        const fAct = btn.getAttribute('data-action');
                        try {
                            const rb = await fetch('../../app/controller/perfil_controller/respond_invite.php', {
                                method: 'POST',
                                headers: {'Content-Type':'application/json'},
                                body: JSON.stringify({friendship_id: fId, action: fAct})
                            });
                            const dat = await rb.json();
                            if(dat.status === 'success') {
                                showToast(dat.message, false);
                                carregarNotificacoes(); 
                                loadMyFriends(); 
                            } else {
                                showToast(dat.message, true);
                            }
                        } catch(err) { console.error('Erro Request Reponse:', err); showToast('Erro ao responder convite.', true); }
                    });
                });

            }
        } catch(err) { console.error('Erro Inbox Leitura:', err); showToast('Erro ao carregar notificações.', true); }
    };

    carregarNotificacoes();

    if(btnCloseNotifications) {
        btnCloseNotifications.addEventListener('click', () => { modalNotifications.classList.add('d-none'); });
    }

    const searchInput = document.getElementById('searchFriendInput');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout = null;

    if(searchInput && searchResults) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value;
            clearTimeout(searchTimeout); 
            
            if(query.length < 2) {
                searchResults.innerHTML = '';
                return;
            }
            
            searchResults.innerHTML = '<p class="text-center text-muted">Buscando usuários no sistema...</p>';

            searchTimeout = setTimeout(async () => {
                try {
                    const req = await fetch('../../app/controller/perfil_controller/search_users.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({query: query})
                    });
                    const dr = await req.json();
                    
                    if(dr.status === 'success') {
                        searchResults.innerHTML = '';
                        if(dr.data.length === 0) {
                            searchResults.innerHTML = '<p class="text-center text-muted">Nenhum ser encontrado batendo as constelações das letras.</p>';
                            return;
                        }
                        
                        dr.data.forEach(usr => {
                            const fallbackPic = `https://ui-avatars.com/api/?background=4f46e5&color=fff&name=${encodeURIComponent(usr.first_name + ' ' + (usr.last_name||''))}`;
                            const htmlU = `
                            <div style="display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,0.05); padding:1rem; border-radius:12px; margin-bottom:10px;">
                                <div style="display:flex; align-items:center; gap:15px;">
                                    <img src="${usr.profile_pic || '../assets/files/default_avatar.png'}" style="width:45px; height:45px; border-radius:50%; object-fit:cover;" onerror="this.src='${fallbackPic}'">
                                    <strong style="color:var(--text-main);">${usr.first_name} ${usr.last_name || ''}</strong>
                                </div>
                                <button type="button" class="btn-primary acao-enviar-convite" data-targetid="${usr.user_id}" style="padding:0.4rem 1.2rem; font-size:0.9rem;">+ Conectar!</button>
                            </div>
                            `;
                            searchResults.innerHTML += htmlU;
                        });

                        document.querySelectorAll('.acao-enviar-convite').forEach(btn => {
                            btn.addEventListener('click', async (ev) => {
                                ev.preventDefault();
                                const rId = btn.getAttribute('data-targetid');
                                const origTex = btn.textContent;
                                btn.textContent = 'Enviando sinal...';
                                btn.disabled = true;
                                try {
                                    const si = await fetch('../../app/controller/perfil_controller/send_invite.php', {
                                        method: 'POST',
                                        headers: {'Content-Type': 'application/json'},
                                        body: JSON.stringify({receiver_id: rId})
                                    });
                                    const siDat = await si.json();
                                    showToast(siDat.message, siDat.status !== 'success');
                                    if(siDat.status === 'success') { btn.textContent = 'Enviado ✔️'; }
                                    else { btn.textContent = origTex; btn.disabled = false; }
                                } catch(e) { console.error(e); showToast('Erro ao enviar convite.', true); }
                            });
                        });
                    } else if (dr.status === 'empty') {
                        searchResults.innerHTML = '';
                    } else {
                        searchResults.innerHTML = `<p class="text-danger text-center">API Erro: ${dr.message}</p>`;
                    }
                } catch(err) {
                    searchResults.innerHTML = `<p class="text-danger text-center">Falha Assíncrona no Servidor de Relacionamentos.</p>`;
                }
            }, 600); 
        });
    }

    const containerAmigos = document.getElementById('myFriendsListContainer');
    const loadMyFriends = async () => {
        if(!containerAmigos) return;
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const visitorId = urlParams.get('id') ? `?user_id=${urlParams.get('id')}` : '';
            const req = await fetch(`../../app/controller/perfil_controller/load_friends.php${visitorId}`);
            const data = await req.json();
            
            if(data.status === 'success') {
                containerAmigos.innerHTML = '';
                if(data.data.length === 0) {
                    containerAmigos.innerHTML = '<div class="glass-card full-box" style="text-align: center;"><p class="empty-state">Essa área não possui amigos visíveis ainda na rede.</p></div>';
                    return;
                }
                
                let htmlHTML = '<div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; padding: 10px;">';
                data.data.forEach(amg => {
                    const fallbackPic = `https://ui-avatars.com/api/?background=4f46e5&color=fff&name=${encodeURIComponent(amg.first_name + ' ' + (amg.last_name||''))}`;
                    const profPic = amg.profile_pic || '../assets/files/default_avatar.png';
                    htmlHTML += `
                    <div class="glass-card" style="display:flex; flex-direction:column; align-items:center; padding: 2rem; border-radius: 16px; transition: transform 0.3s ease; border: 1px solid rgba(255,255,255,0.1);">
                        <img src="${profPic}" style="width: 90px; height: 90px; border-radius:50%; object-fit:cover; margin-bottom: 1rem; border: 3px solid rgba(255,255,255,0.1);" onerror="this.src='${fallbackPic}'">
                        <strong style="color:var(--text-main); font-size:1.1rem;">${amg.first_name} ${amg.last_name||''}</strong>
                        <div style="margin-top:1.5rem; width:100%;">
                            <a href="perfil.php?id=${amg.user_id}" class="btn-outline" style="width:100%; display:block; text-align:center; padding: 0.6rem; text-decoration:none; color:inherit;">👤 Ver Perfil</a>
                        </div>
                    </div>
                    `;
                });
                htmlHTML += '</div>';
                containerAmigos.innerHTML = htmlHTML;
            }
        } catch(err) { console.error('Erro ao carregar Amigos Aba:', err); showToast('Erro ao carregar lista de amigos.', true); }
    };
    
    loadMyFriends();

    const btnUnfriend = document.getElementById('btnUnfriendAction');
    if(btnUnfriend) {
        btnUnfriend.addEventListener('click', async (e) => {
            e.preventDefault();
            showConfirm('💔 Extermínio Relacional! Tem absoluta certeza que deseja Desfazer esta linda e formidável Amizade cortando os laços pro Banco de Dados?', async () => {
            
            const tId = btnUnfriend.getAttribute('data-targetid');
            try {
                const uf = await fetch('../../app/controller/perfil_controller/remove_friend.php', {
                    method: 'POST', headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({friend_id: tId})
                });
                const ufDat = await uf.json();
                showToast(ufDat.message, ufDat.status !== 'success');
                if(ufDat.status === 'success') location.reload(); 
            } catch(e) { console.error('Erro Unfriend Fatal Pdo', e); showToast('Erro ao desfazer amizade.', true); }
        });
        });
    }

    document.querySelectorAll('.acao-enviar-convite-perfil').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const rId = btn.getAttribute('data-targetid');
            const originTxt = btn.textContent;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            btn.textContent = 'Enviando solicitação...';
            btn.disabled = true;
            try {
                const si = await fetch('../../app/controller/perfil_controller/send_invite.php', {
                    method: 'POST', headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({receiver_id: rId})
                });
                const siDat = await si.json();
                if(siDat.status === 'success') {
                    showToast("Solicitação enviada com sucesso!", false);
                    btn.textContent = 'Enviado ✔️';
                } else { 
                    showToast(siDat.message, true);
                    btn.textContent = originTxt; 
                    btn.disabled = false; 
                }
            } catch(e) { console.error('Erro Send Pdo', e); showToast('Erro ao enviar convite.', true); }
        });
    });

    const btnEditAvatar = document.querySelector('.btn-edit-avatar');
    if(btnEditAvatar) btnEditAvatar.addEventListener('click', (e) => { e.preventDefault(); showToast('Em desenvolvimento Módulos Futuros!', true);});
    
    const btnEditWallpaper = document.querySelector('.btn-edit-wallpaper');
    if(btnEditWallpaper) btnEditWallpaper.addEventListener('click', (e) => { e.preventDefault(); showToast('Em desenvolvimento Módulos Futuros!', true);});
});
