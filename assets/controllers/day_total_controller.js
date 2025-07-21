import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    connect() {
        document.addEventListener('Purchase', this.updateTotal)
    }

    updateTotal = () => {
        fetch('/daytotal', {
            method: 'POST'
        }).then(response => {
            return response.json()
        }).then(data => {
            this.element.innerHTML = data.total + ' €'
        })
    }

    disconnect() {
        document.removeEventListener('Purchase', this.updateTotal)
        super.disconnect();
    }
}
