import { Controller } from '@hotwired/stimulus';
import Notifier from "../services/Notifier";


export default class extends Controller {
    connect() {
        this.ids = []
        this.purchases = document.querySelectorAll(`.purchase-line[data-account="${this.element.dataset.account}"`)
        if(this.purchases.length > 0) {
            this.ids = Array.from(this.purchases).map(el => el.dataset.purchaseId)
        }
        this.container = document.querySelector('#cashbox-resume')

    }

    /**
     * Save all payments in account for one person
     *
     * @param e
     * @returns {Promise<void>}
     */
    checkout = async (e) => {
        e.preventDefault()

        if(e.currentTarget.dataset.paymentMode) {

            let paymentMode = e.currentTarget.dataset.paymentMode
            const route = `/checkout/${paymentMode}`

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
                this.refresh()

                this.purchases.forEach(purchase => {
                    const paymentModeCell = purchase.querySelector('td.payment-mode')
                    paymentModeCell.innerText = this.paymentModeValueToShow(paymentMode) + '.'
                    purchase.classList.remove('bg-gray-200')
                })

            }

        }

    }

    /**
     * Make payment mode compliant with previous showed data
     * For exemple payment mode as CB will be shown as Cb
     *
     * @param paymentMode
     * @returns {string}
     */
    paymentModeValueToShow = (paymentMode) => {
        paymentMode = paymentMode.toLowerCase()
        paymentMode = paymentMode.charAt(0).toUpperCase() + paymentMode.slice(1)
        return paymentMode
    }


    /**
     * Refresh the resume zone with new account data
     * TODO move in a service
     * @returns {Promise<void>}
     */
    refresh = async () => {

        const response = await fetch('/resume', {
            method: 'GET'
        })

        if (response.status !== 200) {
            Notifier.error('Erreur lors de la mise à jour des données')
            return
        }

        const html = await response.text()

        this.container.innerHTML = html
    }
}
