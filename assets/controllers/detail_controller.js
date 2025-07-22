import { Controller } from '@hotwired/stimulus';
import Notifier from "../services/Notifier";

export default class extends Controller {

    static targets = [ "list" ]
    connect() {
        this.container = document.querySelector('#cashbox-resume')
    }


    show = (e) => {
        e.preventDefault()
        this.listTarget.classList.add('visible')
    }

    hide = (e) => {
        e.preventDefault()
        this.listTarget.classList.remove('visible')
    }

    remove = async (e) => {
        e.preventDefault()
        const btn = e.currentTarget
        const purchaseId = btn.dataset?.purchaseId

        if(purchaseId) {

            if(confirm('Êtes vous sur de vouloir supprimer cette ligne ?')) {
                const route = `/purchase/delete/${purchaseId}`
                const response = await fetch(route, {
                    method: 'DELETE'
                })

                if (response.status === 201) {
                    Notifier.success('Achat supprimé')
                    btn.closest('.purchase-line')?.remove()
                    await this.refresh()
                }
            }
        }

    }

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
