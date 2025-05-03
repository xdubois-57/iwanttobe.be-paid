class OverlayObjectHelper {
    constructor() {
        this.active = false;
        this.overlay = null;
        this.title = null;
        this.heartCount = 0;
    }

    activate() {
        if (this.active) return;
        this.active = true;
        this.overlay = document.createElement('div');
        this.overlay.style.position = 'fixed';
        this.overlay.style.top = '0';
        this.overlay.style.left = '0';
        this.overlay.style.width = '100vw';
        this.overlay.style.height = '100vh';
        this.overlay.style.zIndex = '11000';
        this.overlay.style.background = 'transparent';
        this.overlay.style.pointerEvents = 'none';
        this.overlay.id = 'overlay-object-helper';
        document.body.appendChild(this.overlay);
        this.render();
        console.log('[OverlayObjectHelper] Activated:', { title: this.title });
    }

    deactivate() {
        if (!this.active) return;
        this.active = false;
        if (this.overlay) {
            document.body.removeChild(this.overlay);
            this.overlay = null;
        }
    }

    addTitle(titleText) {
        this.title = titleText;
        this.render();
        console.log('[OverlayObjectHelper] Title set:', { title: this.title });
    }

    removeTitle() {
        this.title = null;
        this.render();
    }

    addHeart(count = 0) {
        this.heartCount = count;
        this.render();
    }

    setHeartCount(count = 0) {
        this.heartCount = count;
        this.render();
    }

    removeHeart() {
        this.heartCount = null;
        this.render();
    }

    render() {
        if (!this.overlay) return;
        
        console.log('[OverlayObjectHelper] Rendering overlay:', { 
            active: this.active, 
            title: this.title, 
            heartCount: this.heartCount,
            overlay: this.overlay ? 'created' : 'null'
        });
        
        this.overlay.innerHTML = '';
        
        // Render title
        if (this.title) {
            console.log('[OverlayObjectHelper] Rendering title:', this.title);
            const titleDiv = document.createElement('div');
            titleDiv.textContent = this.title;
            titleDiv.style.position = 'absolute';
            titleDiv.style.top = '40px';
            titleDiv.style.left = '50%';
            titleDiv.style.transform = 'translateX(-50%)';
            titleDiv.style.fontSize = '2.5rem';
            titleDiv.style.fontWeight = 'bold';
            titleDiv.style.color = '#ffffff';
            titleDiv.style.background = 'transparent';
            titleDiv.style.padding = '16px 32px';
            titleDiv.style.textShadow = '0 2px 4px rgba(0,0,0,0.5)';
            titleDiv.style.maxWidth = '80vw';
            titleDiv.style.textAlign = 'center';
            titleDiv.style.zIndex = '11001'; // Ensure it's above the canvas
            this.overlay.appendChild(titleDiv);
        } else {
            console.warn('[OverlayObjectHelper] No title to render');
        }
        
        // Render heart and counter
        if (typeof this.heartCount === 'number') {
            const heartBlock = document.createElement('div');
            heartBlock.style.position = 'absolute';
            heartBlock.style.top = '32px';
            heartBlock.style.right = '48px';
            heartBlock.style.display = 'flex';
            heartBlock.style.alignItems = 'center';
            heartBlock.style.fontSize = '2.2rem';
            heartBlock.style.color = '#ffffff';
            heartBlock.style.textShadow = '0 2px 4px rgba(0,0,0,0.5)';
            
            // Number
            const number = document.createElement('span');
            number.textContent = this.heartCount;
            number.style.marginRight = '8px';
            
            // Heart emoji
            const heart = document.createElement('span');
            heart.textContent = '❤️';
            
            // Add to block
            heartBlock.appendChild(number);
            heartBlock.appendChild(heart);
            
            // Add to overlay
            this.overlay.appendChild(heartBlock);
        }
    }
}

// Create a global instance
window.OverlayObjectHelper = new OverlayObjectHelper();