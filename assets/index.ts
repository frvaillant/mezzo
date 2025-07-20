


document.addEventListener('DOMContentLoaded', () => {
    const btn = document.querySelector('#loader') as HTMLButtonElement
    if(btn) {
        btn.addEventListener('click', (e: MouseEvent) => {
            e.preventDefault()
            alert('ca marche')
        })
    }
})
