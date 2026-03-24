const SITE_ROOT = '../../app/controller/comment_controller/';

const loadCommentsTree = async (feelingId) => {
    const list = document.getElementById(`comments-list-${feelingId}`);
    list.innerHTML = `<p style="text-align:center; font-size:0.8rem; color:#888;">Carregando rede social...</p>`;
    try {
        const res = await fetch(`${SITE_ROOT}load_comments.php?feeling_id=${feelingId}`);
        const data = await res.json();
        if (data.status === 'success') {
            if (data.data.length === 0) {
                list.innerHTML = `<p style="text-align:center; font-size:0.8rem; color:#888;">Nenhum comentário ainda. Seja o primeiro da roda!</p>`;
                return;
            }
            
            // Map Root/Children for the Thread Tree
            const commentsMap = {};
            data.data.forEach(c => {
                c.children = [];
                commentsMap[c.coment_id] = c;
            });
            const rootComments = [];
            data.data.forEach(c => {
                if (c.parent_id) {
                    if(commentsMap[c.parent_id]) commentsMap[c.parent_id].children.push(c);
                } else {
                    rootComments.push(c);
                }
            });

            // Recursive Builder (Engine CSS Glassmorphism)
            const renderTree = (nodeArray) => {
                let html = '';
                nodeArray.forEach(c => {
                    const fallbackPic = `https://ui-avatars.com/api/?background=4f46e5&color=fff&name=${encodeURIComponent(c.first_name)}`;
                    const isOwner = (c.user_id == data.session_user);
                    const isPostOwner = (c.post_owner_id == data.session_user);
                    
                    let actionsHtml = `<a href="#" class="reply-action reply-comment" data-cid="${c.coment_id}" data-id="${feelingId}" data-name="${c.first_name}">Responder</a>`;
                    
                    if (isOwner) {
                        actionsHtml += ` | <a href="#" class="edit-comment" data-cid="${c.coment_id}" data-id="${feelingId}" data-text="${encodeURIComponent(c.coment)}">✎ Editar</a>`;
                    }
                    if (isOwner || isPostOwner) {
                        actionsHtml += ` | <a href="#" class="delete-action del-comment" data-cid="${c.coment_id}" data-id="${feelingId}">✕ Apagar</a>`;
                    }
                    
                    let avatarUrl = fallbackPic;
                    if (c.profile_pic) {
                        avatarUrl = c.profile_pic.startsWith('http') ? c.profile_pic : '../' + c.profile_pic;
                    }
                    
                    html += `
                    <div class="comment-wrapper">
                        <div class="comment-item" id="comment-box-${c.coment_id}">
                            <img src="${avatarUrl}" class="comment-avatar" onerror="this.src='${fallbackPic}'">
                            <div class="comment-bubble">
                                <strong class="comment-author">${c.first_name} ${c.last_name}</strong>
                                <p class="comment-text" id="comment-text-view-${c.coment_id}">${c.coment}</p>
                                <div class="comment-actions">
                                    <span class="comment-date">${new Date(c.created_at).toLocaleString('pt-BR').substring(0, 16)}</span>
                                    ${actionsHtml}
                                </div>
                            </div>
                        </div>`;
                        
                    if (c.children.length > 0) {
                        html += `
                        <div class="comment-children">
                            ${renderTree(c.children)}
                        </div>`;
                    }
                    html += `</div>`;
                });
                return html;
            };
            list.innerHTML = renderTree(rootComments);
        } else {
            list.innerHTML = `<p style="text-align:center; font-size:0.8rem; color:red;">${data.message}</p>`;
        }
    } catch(e) {
        list.innerHTML = `<p style="text-align:center; font-size:0.8rem; color:red;">Erro fatal API.</p>`;
    }
};

// Listeners V-DOM
document.addEventListener('click', async (e) => {
    // Toggle Section
    if (e.target.closest('.acao-abrir-comments')) {
        e.preventDefault();
        const btn = e.target.closest('.acao-abrir-comments');
        const fId = btn.getAttribute('data-id');
        const section = document.getElementById(`comments-section-${fId}`);
        section.classList.toggle('d-none');
        if(!section.classList.contains('d-none')) loadCommentsTree(fId);
    }
    
    // Responder (Seta parent ID e formata Action)
    if (e.target.closest('.reply-comment')) {
        e.preventDefault();
        const btn = e.target.closest('.reply-comment');
        const cId = btn.getAttribute('data-cid');
        const fId = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        
        const actionBadge = document.getElementById(`action-badge-${fId}`);
        const actionText = document.getElementById(`action-text-${fId}`);
        const input = document.getElementById(`comment-input-${fId}`);
        const btnSubmit = document.querySelector(`.acao-enviar-comment[data-id="${fId}"]`);
        
        actionBadge.classList.remove('d-none');
        actionText.innerHTML = `Respondendo a <strong style='color:#fff;'>${name}</strong>`;
        
        input.setAttribute('data-parent-id', cId);
        input.removeAttribute('data-edit-id');
        input.value = '';
        input.style.height = ''; 
        btnSubmit.textContent = 'Publicar';
        input.focus();
    }
    
    // Edita Linha (Joga p/ a mesma barra)
    if (e.target.closest('.edit-comment')) {
        e.preventDefault();
        const btn = e.target.closest('.edit-comment');
        const cId = btn.getAttribute('data-cid');
        const fId = btn.getAttribute('data-id');
        const rawTxt = decodeURIComponent(btn.getAttribute('data-text'));
        
        const actionBadge = document.getElementById(`action-badge-${fId}`);
        const actionText = document.getElementById(`action-text-${fId}`);
        const input = document.getElementById(`comment-input-${fId}`);
        const btnSubmit = document.querySelector(`.acao-enviar-comment[data-id="${fId}"]`);
        
        // Populate
        input.value = rawTxt;
        input.style.height = 'auto'; 
        // Atraso sutil para o DOM computar o scroll antes de atruibir px
        setTimeout(() => { input.style.height = input.scrollHeight + 'px'; }, 10);
        
        // Conversão de Estado Pleno p/ Edição
        actionBadge.classList.remove('d-none');
        actionText.innerHTML = `Editando comentário...`;
        
        input.setAttribute('data-edit-id', cId);
        input.removeAttribute('data-parent-id');
        btnSubmit.textContent = 'Salvar Edição';
        input.focus();
    }
    
    // Cancelar Escrita (Seja Edit ou Reply)
    if (e.target.closest('.cancel-action')) {
        e.preventDefault();
        const btn = e.target.closest('.cancel-action');
        const fId = btn.getAttribute('data-id');
        
        const actionBadge = document.getElementById(`action-badge-${fId}`);
        const input = document.getElementById(`comment-input-${fId}`);
        const btnSubmit = document.querySelector(`.acao-enviar-comment[data-id="${fId}"]`);
        
        actionBadge.classList.add('d-none');
        input.removeAttribute('data-parent-id');
        input.removeAttribute('data-edit-id');
        input.value = '';
        input.style.height = ''; // Reset CSS px
        btnSubmit.textContent = 'Publicar';
    }
    
    // Apaga Linha (Firewall Duplo)
    if (e.target.closest('.del-comment')) {
        e.preventDefault();
        showConfirm("Deletar definitivamente este comentário e todas as respostas (filhas) abaixo dele?", async () => {
        const btn = e.target.closest('.del-comment');
        const cId = btn.getAttribute('data-cid');
        const fId = btn.getAttribute('data-id');
        try {
            const res = await fetch(`${SITE_ROOT}delete_comment.php`, {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({comment_id: cId})
            });
            const dat = await res.json();
            if(dat.status === 'success') {
                showToast("Comentário apagado e filhos cascateados.", false);
                // Decrementar o contador mestre (ID unificado)
                const countEl = document.getElementById(`master-comments-count-${fId}`);
                if (countEl) {
                    let currentCount = parseInt(countEl.textContent) || 0;
                    countEl.textContent = Math.max(0, currentCount - 1);
                }
                loadCommentsTree(fId);
            }
            else showToast(dat.message, true);
        } catch(err){}
        });
    }
    
    // Master Submit (Cérebro Duplo INSERT vs UPDATE)
    if (e.target.closest('.acao-enviar-comment')) {
        const btn = e.target.closest('.acao-enviar-comment');
        const fId = btn.getAttribute('data-id');
        const input = document.getElementById(`comment-input-${fId}`);
        const text = input.value.trim();
        
        const isEditing = input.hasAttribute('data-edit-id');
        const editId = input.getAttribute('data-edit-id');
        const parentId = input.getAttribute('data-parent-id');
        
        if(!text) return;
        if(text.length > 1500) {
            showToast("O comentário deve respeitar no máximo os 1500 limitadores MySQL.", true); return;
        }
        
        btn.innerHTML = '⚙️..'; btn.disabled = true;
        try {
            let res;
            if (isEditing) {
                // UPDATE Route API
                res = await fetch(`${SITE_ROOT}edit_comment.php`, {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({comment_id: editId, coment: text})
                });
            } else {
                // INSERT Route API
                const payload = {feeling_id: fId, coment: text};
                if(parentId) payload.parent_id = parentId;
                res = await fetch(`${SITE_ROOT}add_comment.php`, {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    body: JSON.stringify(payload)
                });
            }
            
            const dat = await res.json();
            if(dat.status === 'success') {
                // Reseta a UI ao normal
                input.value = '';
                input.style.height = '';
                document.getElementById(`action-badge-${fId}`).classList.add('d-none');
                input.removeAttribute('data-parent-id');
                input.removeAttribute('data-edit-id');
                btn.textContent = 'Publicar';
                
                // Recarrega mural local
                showToast(dat.message || "Publicado!", false);
                // Incrementar o contador mestre (ID unificado)
                const countEl = document.getElementById(`master-comments-count-${fId}`);
                if (countEl) {
                    let currentCount = parseInt(countEl.textContent) || 0;
                    countEl.textContent = currentCount + 1;
                }
                loadCommentsTree(fId);
            } else { showToast(dat.message, true); }
        } catch(err){}
        finally { 
            // Só reseta texto mecânico se falhou o rest, pq no sucesso ele reseta via if puro
            if (btn.textContent === '⚙️..') {
                btn.innerHTML = isEditing ? 'Salvar Edição' : 'Publicar';
            }
            btn.disabled = false; 
        }
    }
});
