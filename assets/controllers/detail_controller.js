import { Controller } from '@hotwired/stimulus';
import Notifier from "../services/Notifier";
import customConfirm from "../services/Confirm";

export default class extends Controller {

    static targets = [ "list", "undo", "undotimer" ]

    connect() {
        this.container = document.querySelector('#cashbox-resume')
        this.interval = null
        this.waitForDelete = null
        this.canRemove = false
    }

    show = (e) => {
        e.preventDefault()
        this.listTarget.classList.add('visible')
    }

    hide = (e) => {
        e.preventDefault()
        this.listTarget.classList.remove('visible')
    }

    toggleUndoVisibility = () => {
        this.undoTarget.classList.toggle('hidden')
        this.undoTarget.classList.toggle('flex')
    }

    undoRemove = (e) => {
        e.preventDefault()
        clearInterval(this.interval)
        clearTimeout(this.waitForDelete)
        this.canRemove = false
        this.toggleUndoVisibility()
    }

    launchRemove = async (purchaseId, btn) => {
        if(this.canRemove) {
            const route = `/purchase/delete/${purchaseId}`
            const response = await fetch(route, {
                method: 'DELETE'
            })

            if (response.status === 201) {
                Notifier.success('Achat supprimé')
                btn.closest('.purchase-line')?.remove()
                await this.refresh()
                this.toggleUndoVisibility()
            }
        }
    }

    startInterval = (time) => {
        this.undotimerTarget.innerText = time
        this.interval = setInterval(() => {
            time -= 1
            this.undotimerTarget.innerText = time
            if(time === 0) {
                clearInterval(this.interval)
            }
        }, 1000)
    }

    remove = async (e) => {
        e.preventDefault()

        const btn = e.currentTarget
        const purchaseId = btn.dataset?.purchaseId

        if(purchaseId) {
            this.canRemove = true
            const allow = await customConfirm("Voulez-vous vraiment supprimer cet élément ?");
            if(allow) {
                this.toggleUndoVisibility()

                this.startInterval(5)

                this.waitForDelete = setTimeout(async () => {
                    await this.launchRemove(purchaseId, btn)
                }, 5500)

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
