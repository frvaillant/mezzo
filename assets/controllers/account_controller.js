import { Controller } from '@hotwired/stimulus';
import Notifier from "../services/Notifier";


export default class extends Controller {
    connect() {
        this.ids = []
        this.purchases = document.querySelectorAll(`.purchase-line[data-account="${this.element.dataset.account}"`)
        if(this.purchases.length > 0) {
            this.ids = Array.from(this.purchases).map(el => el.dataset.purchaseId)
        }

    }

    checkout = async (e) => {
        e.preventDefault()

        if(e.currentTarget.dataset.paymentMode) {

            const route = `/checkout/${e.currentTarget.dataset.paymentMode}`
            const data = {
                ids: this.ids,
                mode: e.currentTarget.dataset.paymentMode
            }

            const response = await fetch(route, {
                method: 'POST',
                body: JSON.stringify(data)
            })

            if(response.status === 200) {
                Notifier.success('Paiement enregistré')
                this.element.remove()

                this.purchases.forEach(purchase => {
                    purchase.classList.remove('bg-cyan-400')
                })
            }

        }

    }
}
