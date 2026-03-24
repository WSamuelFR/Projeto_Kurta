// public/assets/js/utils.js

// Auto-Injec CSS V-DOM
const injectUtilsCSS = () => {
    if(document.getElementById('cortex-utils-css')) return;
    const style = document.createElement('style');
    style.id = 'cortex-utils-css';
    style.innerHTML = `
        /* Toast Styles */
        #cortex-toast-container {
            position: fixed;
            top: 25px;
            right: 25px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
        }
        .cortex-toast {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border-left: 5px solid var(--neon-main);
            color: #fff;
            padding: 18px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            font-size: 0.95rem;
            min-width: 280px;
            max-width: 400px;
            animation: slideInRight 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            pointer-events: all;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cortex-toast.toast-error { border-left-color: #ef4444; }
        .cortex-toast.toast-success { border-left-color: #10b981; }
        .cortex-toast.toast-info { border-left-color: #3b82f6; }
        
        .toast-body { flex: 1; margin-right: 15px; line-height: 1.5; word-break: break-word; font-weight: 500; }
        .toast-close { cursor: pointer; color: #888; font-weight: bold; background: none; border: none; font-size: 1.4rem; transition: color 0.2s; }
        .toast-close:hover { color: #fff; }
        
        @keyframes slideInRight {
            from { transform: translateX(120%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(120%); opacity: 0; }
        }

        /* Modal Blur Styles */
        #cortex-modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(8px);
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeInOverlay 0.2s ease-out;
        }
        .cortex-modal-box {
            background: rgba(15, 23, 42, 0.95);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 30px;
            width: 90%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0,0,0,0.6);
            animation: zoomInModal 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        .cortex-modal-title { color: #fff; font-size: 1.4rem; margin-bottom: 15px; font-weight: 700; }
        .cortex-modal-body { color: rgba(255,255,255,0.7); font-size: 1.05rem; margin-bottom: 30px; line-height: 1.6; }
        .cortex-modal-actions { display: flex; gap: 15px; justify-content: center; }
        .cortex-btn { padding: 10px 25px; border-radius: 25px; border: none; font-weight: 600; cursor: pointer; transition: all 0.2s; font-size: 0.95rem; }
        .cortex-btn-cancel { background: rgba(255,255,255,0.1); color: #fff; }
        .cortex-btn-cancel:hover { background: rgba(255,255,255,0.2); }
        .cortex-btn-confirm { background: #ef4444; color: #fff; }
        .cortex-btn-confirm:hover { background: #dc2626; box-shadow: 0 0 15px rgba(239, 68, 68, 0.5); transform: translateY(-2px); }
        
        @keyframes fadeInOverlay {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes zoomInModal {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
};

// Injeta Base CSS automaticamente
injectUtilsCSS();

// Toast Engine Global
window.showToast = (message, isError = false) => {
    let container = document.getElementById('cortex-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'cortex-toast-container';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `cortex-toast ${isError ? 'toast-error' : 'toast-success'}`;
    toast.innerHTML = `
        <div class="toast-body">${message}</div>
        <button class="toast-close">&times;</button>
    `;
    
    container.appendChild(toast);
    
    const removeToast = () => {
        toast.style.animation = 'slideOutRight 0.3s ease-out forwards';
        setTimeout(() => { if(toast.parentNode) toast.remove(); }, 350);
    };
    
    toast.querySelector('.toast-close').addEventListener('click', removeToast);
    // Auto Remove em 4 Segundos
    setTimeout(removeToast, 4500);
};

// Modal Engine Global
window.showConfirm = (message, onConfirm) => {
    const overlay = document.createElement('div');
    overlay.id = 'cortex-modal-overlay';
    
    overlay.innerHTML = `
        <div class="cortex-modal-box">
            <div class="cortex-modal-title">Ação Irreversível</div>
            <div class="cortex-modal-body">${message}</div>
            <div class="cortex-modal-actions">
                <button class="cortex-btn cortex-btn-cancel" id="cortex-cancel-btn">Cancelar Operação</button>
                <button class="cortex-btn cortex-btn-confirm" id="cortex-confirm-btn">Sim, Prosseguir</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
    
    const closeModal = () => {
        overlay.style.animation = 'fadeInOverlay 0.2s ease-out reverse';
        overlay.querySelector('.cortex-modal-box').style.animation = 'zoomInModal 0.2s ease-out reverse';
        setTimeout(() => { if(overlay.parentNode) overlay.remove(); }, 200);
    };
    
    document.getElementById('cortex-cancel-btn').addEventListener('click', closeModal);
    document.getElementById('cortex-confirm-btn').addEventListener('click', () => {
        closeModal();
        if(typeof onConfirm === 'function') onConfirm();
    });
};
