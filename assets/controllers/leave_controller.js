import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    connect() {

    }

    go = (e) => {
        e.preventDefault()
        const href = e.currentTarget.getAttribute('href')
        this.element.classList.add('leave')
        setTimeout(() => {
            window.location.href = href
        }, 500)
    }


}
