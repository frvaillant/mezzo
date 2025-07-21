import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.updateTime();
        this.interval = setInterval(() => this.updateTime(), 1000);
    }

    updateTime() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');

        this.element.innerHTML = `${h}:${m}:${s}`;
    }


    disconnect() {
        clearInterval(this.interval); // nettoyage pour éviter les fuites mémoire
    }
}
