import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ["form"]

    connect() {

    }

    showForm = () => {
        this.formTarget.classList.remove('hidden')
    }

    hideForm = () => {
        this.formTarget.classList.add('hidden')
    }


    disconnect() {

    }
}
