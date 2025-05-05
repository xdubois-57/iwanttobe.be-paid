// EventQrBlock: Dynamically build a QR/event info block with AJAX QR SVG fetch
// Usage: new EventQrBlock(container, url, eventCode, eventPassword)

class EventQrBlock {
    constructor(container, url, eventCode, eventPassword = null, additionalText = '', showShareButton = true) {
        this.container = (typeof container === 'string') ? document.querySelector(container) : container;
        this.url = url;
        this.eventCode = eventCode;
        this.eventPassword = eventPassword;
        this.additionalText = additionalText;
        this.showShareButton = showShareButton;
        this.render();
    }

    async render() {
        this.container.innerHTML = '';
        // QR code SVG via AJAX
        const qrDiv = document.createElement('div');
        qrDiv.style.maxWidth = '200px';
        qrDiv.style.margin = '0 auto';
        const qrSvg = await this.fetchQrSvg(this.url);
        qrDiv.innerHTML = qrSvg;
        this.container.appendChild(qrDiv);
        // Event info
        const infoDiv = document.createElement('div');
        infoDiv.style.marginTop = '0.5em';
        infoDiv.style.fontSize = '0.95em';
        infoDiv.style.color = 'var(--text-primary, #333)';
        infoDiv.innerHTML = `<div><strong>${this.getTranslation('event_code')}</strong> ${this.escape(this.eventCode)}</div>`;
        this.container.appendChild(infoDiv);
        // Share button - only add if showShareButton is true
        if (this.showShareButton) {
            const shareBtn = document.createElement('button');
            shareBtn.type = 'button';
            shareBtn.textContent = this.getTranslation('share_button');
            shareBtn.style.marginTop = '1em';
            shareBtn.style.fontSize = '0.95em';
            shareBtn.style.padding = '0.5em 1.2em';
            shareBtn.style.cursor = 'pointer';
            shareBtn.onclick = () => this.share();
            this.container.appendChild(shareBtn);
        }
        // Additional text - if provided
        if (this.additionalText) {
            const textDiv = document.createElement('div');
            textDiv.style.marginTop = '0.8em';
            textDiv.style.fontSize = '0.9em';
            textDiv.style.color = 'var(--text-secondary, #555)';
            textDiv.innerHTML = this.escape(this.additionalText);
            this.container.appendChild(textDiv);
        }
    }

    // Get translation from window.t function if available
    getTranslation(key) {
        // If window.t is available (from the main app), use it
        if (typeof window.t === 'function') {
            return window.t(key);
        }
        
        // Default translations if window.t is not available
        const translations = {
            'event_code': 'Event code:',
            'event_password': 'Event password:',
            'share_button': 'Share',
            'share_title': 'Join my event',
            'share_text': 'Join my event using code:',
            'copy_success': 'Link copied to clipboard!',
            'share_error': 'Could not share. Link copied to clipboard instead.'
        };
        
        // Return translation or key as fallback
        return translations[key] || key;
    }

    // Fetch QR SVG from server
    async fetchQrSvg(url) {
        try {
            const params = new URLSearchParams();
            params.append('data', url);
            const response = await fetch('/qr/svg?' + params.toString());
            if (response.ok) {
                return await response.text();
            }
        } catch (e) {
            console.error('QR fetch error:', e);
        }
        // Fallback if fetch fails - simple placeholder
        return '<div style="width:200px;height:200px;background:#eee;display:flex;align-items:center;justify-content:center;border-radius:5px;">QR Code</div>';
    }

    // Share functionality
    async share() {
        const { url, eventCode, additionalText } = this;
        const shareText = `${this.getTranslation('share_text')} ${eventCode}${additionalText ? '\n' + additionalText : ''}`;
        try {
            if (navigator.share) {
                await navigator.share({
                    title: this.getTranslation('share_title'),
                    text: shareText,
                    url
                });
            } else {
                throw new Error('Web Share API not supported');
            }
        } catch (e) {
            // Fallback - copy to clipboard
            const textarea = document.createElement('textarea');
            textarea.value = `${shareText}\n${url}`;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert(this.getTranslation('copy_success'));
        }
    }

    // Safely escape HTML
    escape(html) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(html));
        return div.innerHTML;
    }
}
