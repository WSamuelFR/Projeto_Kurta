document.addEventListener('DOMContentLoaded', () => {
    
    // ==========================================
    // MÓDULO 1: NAVEGAÇÃO DE ABAS
    // ==========================================
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            
            const target = tab.getAttribute('data-target');
            document.getElementById(target).classList.add('active');
            
            // Lazy load abas
            if(target === 'tab-principal' && !document.getElementById('feedContainerWrapper').dataset.loaded) {
                renderFeedEngine(document.getElementById('feedContainerWrapper'), '../../app/controller/feeling_controller/load_global_feelings.php');
            }
            if(target === 'tab-amigos' && !document.getElementById('feedFriendsContainer').dataset.loaded) {
                renderFeedEngine(document.getElementById('feedFriendsContainer'), '../../app/controller/feeling_controller/load_friends_feelings.php');
            }
            if(target === 'tab-clas' && !document.getElementById('clanTableBody').dataset.loaded) {
                loadClans('meus-clas');
            }
        });
    });



    // ==========================================
    // MÓDULO 3: ENGINE DE RENDERIZAÇÃO DE FEEDS
    // ==========================================
    const renderFeedEngine = async (container, apiUrl) => {
        if (!container) return;
        container.dataset.loaded = 'true';
        container.innerHTML = `<div class="glass-card feed-box"><p class="empty-state text-center">Processando banco de dados...</p></div>`;

        try {
            const response = await fetch(apiUrl);
            const result = await response.json();

            if (result.status === 'success') {
                container.innerHTML = '';
                if (result.data.length === 0) {
                    container.innerHTML = `
                        <div class="glass-card full-box" style="text-align: center;">
                            <p class="empty-state text-muted" style="margin-top:20px;">Nenhuma publicação captada por este sistema ainda!</p>
                        </div>
                    `;
                    return;
                }

                result.data.forEach(post => {
                    const fullName = post.first_name + ' ' + (post.last_name || '');
                    const picture = post.profile_pic || '../assets/files/default_avatar.png';
                    const fallbackPic = `https://ui-avatars.com/api/?background=4f46e5&color=fff&name=${encodeURIComponent(fullName)}`;
                    const objData = new Date(post.created_at);
                    const formatData = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'long', timeStyle: 'short' }).format(objData);

                    const postHTML = `
                    <div class="feed-post-card glass-card" id="post-box-${post.feeling_id}" style="margin-bottom: 25px; transition: all 0.3s ease;">
                        <div class="post-header">
                            <div class="post-author-info">
                                <a href="perfil.php?id=${post.user_id}">
                                    <img src="${picture}" alt="Avatar Autor" class="post-avatar" onerror="this.src='${fallbackPic}'">
                                </a>
                                <a href="perfil.php?id=${post.user_id}" style="text-decoration: none;">
                                    <strong class="post-author-name">${fullName}</strong>
                                </a>
                            </div>
                        </div>

                        <div class="post-body">
                            <p class="post-text-content" style="white-space: pre-wrap; color: var(--text-main); font-size: 1.05rem; line-height: 1.5;" id="text-post-${post.feeling_id}">${post.feeling}</p>
                            <span class="post-date" style="display:flex; align-items:center; gap:8px; font-size: 0.85rem; color: var(--text-muted); margin-top: 10px;">
                                ${formatData}
                            </span>
                        </div>

                        <div class="post-footer">
                            <button class="btn-action acao-abrir-comments" data-id="${post.feeling_id}" style="color: var(--text-muted);"><span style="font-size:1.2rem;">💬</span> <span id="master-comments-count-${post.feeling_id}">${post.comments_count || 0}</span> Comentários</button>
                            <button class="btn-action btn-like" data-id="${post.feeling_id}" style="color: ${post.user_has_liked > 0 ? '#ef4444' : 'var(--text-muted)'};">
                                <span style="font-size:1.2rem;">${post.user_has_liked > 0 ? '❤️' : '🤍'}</span> 
                                <span class="like-count" id="like-count-${post.feeling_id}">${post.likes_count}</span> Curtidas
                            </button>
                            <button class="btn-action dummy-share-btn" style="color: var(--text-muted);"><span style="font-size:1.2rem;">🔄</span> Share</button>
                        </div>
                        
                        <!-- COMMENTS SECTION HIDDEN -->
                        <div class="comments-section d-none" id="comments-section-${post.feeling_id}">
                            <div class="comments-list" id="comments-list-${post.feeling_id}"></div>
                            <div class="comment-input-area">
                                <div id="action-badge-${post.feeling_id}" class="d-none" style="font-size:0.8rem; color:var(--neon-text); margin-bottom:-5px; display:flex; align-items:center;">
                                    <span id="action-text-${post.feeling_id}">Ação</span>
                                    <a href="#" class="cancel-action" data-id="${post.feeling_id}">✕ Cancelar</a>
                                </div>
                                <div class="comment-textarea-wrapper">
                                    <textarea class="comment-textarea" id="comment-input-${post.feeling_id}" placeholder="Escreva seu comentário... (Máx 1500 chars)" maxlength="1500" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                                    <button class="comment-submit-btn acao-enviar-comment" data-id="${post.feeling_id}">Publicar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
                    container.insertAdjacentHTML('beforeend', postHTML);
                });

                // Attach Like listeners to the newly rendered posts
                container.querySelectorAll('.btn-like').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.preventDefault();
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
                                const icon = btn.querySelector('span');
                                if (data.action === 'liked') {
                                    btn.style.color = '#ef4444';
                                    icon.textContent = '❤️';
                                } else {
                                    btn.style.color = 'var(--text-muted)';
                                    icon.textContent = '🤍';
                                }
                            }
                        } catch(err) {}
                    });
                });
                
                container.querySelectorAll('.dummy-share-btn').forEach(b => {
                    b.addEventListener('click', (e) => { e.preventDefault(); window.showToast("Módulo de Compartilhamento em Desenvolvimento!", true); });
                });

            } else {
                container.innerHTML = `<p class="text-danger text-center">Engine Erro: ${result.message}</p>`;
            }
        } catch (err) {
            console.error(err);
            container.innerHTML = `<p class="text-danger text-center">Falha de Servidor ao Sincronizar Feed.</p>`;
        }
    };


    // ==========================================
    // MÓDULO 4: MOTOR DE TIPO CLÃ (TABLE)
    // ==========================================
    const clanTbody = document.getElementById('clanTableBody');
    const pills = document.querySelectorAll('.btn-pill');

    const loadClans = async (filtroStr) => {
        if(!clanTbody) return;
        clanTbody.dataset.loaded = 'true';
        clanTbody.innerHTML = '<tr><td colspan="3" style="text-align: center; color: rgba(255,255,255,0.5); padding: 20px;">Carregando tabelas dos clãs...</td></tr>';
        
        try {
            const res = await fetch(`../../app/controller/clan_controller/load_clans.php?filter=${filtroStr}`);
            const json = await res.json();
            if(json.status === 'success') {
                clanTbody.innerHTML = json.html;
            } else {
                clanTbody.innerHTML = `<tr><td colspan="3" style="text-align: center; color: #ef4444; padding: 20px;">Erro: ${json.message}</td></tr>`;
            }
        } catch(e) {
            clanTbody.innerHTML = '<tr><td colspan="3" style="text-align: center; color: #ef4444; padding: 20px;">Falha de Conexão.</td></tr>';
        }
    };

    if(pills.length > 0) {
        pills.forEach(pill => {
            pill.addEventListener('click', (e) => {
                e.preventDefault();
                pills.forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                
                const filtroCorreto = pill.getAttribute('data-filter');
                loadClans(filtroCorreto);
            });
        });
    }


    // ==========================================
    // AUTO-INIT DA TELA PADRÃO (GLOBAL FEED)
    // ==========================================
    renderFeedEngine(document.getElementById('feedContainerWrapper'), '../../app/controller/feeling_controller/load_global_feelings.php');

});
