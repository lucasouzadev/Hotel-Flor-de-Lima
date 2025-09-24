// Sistema de loading global
class LoadingManager {
    constructor() {
        this.loadingOverlay = null;
        this.createLoadingOverlay();
    }
    
    createLoadingOverlay() {
        this.loadingOverlay = document.createElement('div');
        this.loadingOverlay.id = 'loadingOverlay';
        this.loadingOverlay.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <p>Carregando...</p>
            </div>
        `;
        
        // Adicionar estilos
        const styles = `
            #loadingOverlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                display: none;
                justify-content: center;
                align-items: center;
                z-index: 99999;
                backdrop-filter: blur(5px);
            }
            
            .loading-content {
                text-align: center;
                color: white;
            }
            
            .loading-spinner {
                width: 50px;
                height: 50px;
                border: 4px solid rgba(255, 255, 255, 0.3);
                border-top: 4px solid #8B4513;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 20px;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .loading-content p {
                font-size: 1.2rem;
                font-weight: 600;
                margin: 0;
            }
        `;
        
        const styleSheet = document.createElement('style');
        styleSheet.textContent = styles;
        document.head.appendChild(styleSheet);
        
        document.body.appendChild(this.loadingOverlay);
    }
    
    show(message = 'Carregando...') {
        if (this.loadingOverlay) {
            this.loadingOverlay.querySelector('p').textContent = message;
            this.loadingOverlay.style.display = 'flex';
        }
    }
    
    hide() {
        if (this.loadingOverlay) {
            this.loadingOverlay.style.display = 'none';
        }
    }
}

// Instância global
window.loadingManager = new LoadingManager();

// Função global para mostrar loading
function showLoading(message = 'Carregando...') {
    window.loadingManager.show(message);
}

// Função global para esconder loading
function hideLoading() {
    window.loadingManager.hide();
}
