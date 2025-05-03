// EventQrBlock: Dynamically build a QR/event info block with AJAX QR SVG fetch
// Usage: new EventQrBlock(container, url, eventCode, eventPassword)

class EventQrBlock {
    constructor(container, url, eventCode, eventPassword = null) {
        this.container = (typeof container === 'string') ? document.querySelector(container) : container;
        this.url = url;
        this.eventCode = eventCode;
        this.eventPassword = eventPassword;
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
        infoDiv.style.color = '#333';
        infoDiv.innerHTML = `<div><strong>Event code:</strong> ${this.escape(this.eventCode)}</div>`;
        if (this.eventPassword) {
            const pwdDiv = document.createElement('div');
            pwdDiv.style.marginTop = '0.4em';
            pwdDiv.style.color = '#666';
            pwdDiv.innerHTML = `<strong>Event password:</strong> ${this.escape(this.eventPassword)}`;
            infoDiv.appendChild(pwdDiv);
        }
        this.container.appendChild(infoDiv);
        // Share button
        const shareBtn = document.createElement('button');
        shareBtn.type = 'button';
        shareBtn.textContent = 'Share';
        shareBtn.style.marginTop = '1em';
        shareBtn.style.fontSize = '0.95em';
        shareBtn.style.padding = '0.5em 1.2em';
        shareBtn.style.cursor = 'pointer';
        shareBtn.onclick = () => this.share();
        this.container.appendChild(shareBtn);
    }

    async fetchQrSvg(url) {
        const resp = await fetch(`/qr/svg?data=${encodeURIComponent(url)}`);
        if (!resp.ok) return '<svg width="200" height="200"><text x="10" y="100" font-size="14">QR error</text></svg>';
        return await resp.text();
    }

    escape(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    share() {
        if (navigator.share) {
            navigator.share({ url: this.url })
                .catch(() => {});
        } else if (navigator.clipboard) {
            navigator.clipboard.writeText(this.url).then(() => {
                alert('Link copied to clipboard!');
            }, () => {
                prompt('Copy this link:', this.url);
            });
        } else {
            prompt('Copy this link:', this.url);
        }
    }
}
