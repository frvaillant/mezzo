import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    connect() {

        this.delay = 300_000;
        this.isLocked = false;

        ['touchmove', 'orientationchange', 'visibilitychange', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(event => {
            window.addEventListener(event, this.onUserActivity);
        });


        this.inactivityTimeout = setTimeout(() => {
            this.lock()
        }, this.delay);

        this.onUserActivity()

    }

    onUserActivity = () => {
        clearTimeout(this.inactivityTimeout);
        this.inactivityTimeout = setTimeout(() => {
            this.lock()
        }, this.delay);
    };

    lock = async () => {

        if(!this.isLocked) {
            this.isLocked = true

            await fetch('/lock', {method: 'POST'})

            const overlay = document.createElement('div');
            overlay.id = 'lock-overlay';
            Object.assign(overlay.style, {
                position: 'fixed',
                top: '0',
                left: '0',
                width: '100vw',
                height: '100vh',
                backgroundColor: 'rgba(0, 0, 0, 0.6)',
                zIndex: '9999',
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                color: 'white'
            });


            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/unlock';
            form.name = 'login'
            form.style.background = '#fff';
            form.style.padding = '2rem';
            form.style.borderRadius = '8px';
            form.style.boxShadow = '0 0 20px rgba(0,0,0,0.5)';

            const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            icon.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            icon.setAttribute('width', '60');
            icon.setAttribute('height', '60');
            icon.setAttribute('fill', '#000000');
            icon.setAttribute('viewBox', '0 0 256 256');
            icon.innerHTML = `
      <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm40-104a40,40,0,1,0-65.94,30.44L88.68,172.77A8,8,0,0,0,96,184h64a8,8,0,0,0,7.32-11.23l-13.38-30.33A40.14,40.14,0,0,0,168,112ZM136.68,143l11,25.05H108.27l11-25.05A8,8,0,0,0,116,132.79a24,24,0,1,1,24,0A8,8,0,0,0,136.68,143Z"/>
    `;

            const label = document.createElement('label');
            label.textContent = 'Entrez votre code pour déverrouiller';
            label.htmlFor = 'code';
            label.style.display = 'block';
            label.style.marginBottom = '0.5rem';
            label.style.color = 'black'

            const input = document.createElement('input');
            input.type = 'password';
            input.name = 'login[code]';
            input.id = 'login_code';
            input.required = true;
            input.style.padding = '0.5rem';
            input.style.marginBottom = '1rem';
            input.style.width = '100%';
            input.classList.add('text-black', 'border', 'border-black', 'rounded-md')
            input.setAttribute('autocomplete', 'new-password')

            const submit = document.createElement('button');
            submit.type = 'submit';
            submit.textContent = 'Déverrouiller';
            submit.style.padding = '0.5rem 1rem';
            submit.style.color = '#fff';
            submit.style.border = 'none';
            submit.style.cursor = 'pointer';
            submit.classList.add('rounded-md', 'bg-cyan-500')

            form.appendChild(icon);
            form.appendChild(label);
            form.appendChild(input);
            form.appendChild(submit);
            overlay.appendChild(form);
            document.body.appendChild(overlay);


            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                const response = await fetch('/unlock', {
                    method: 'POST',
                    body: formData,
                });

                if (response.status === 200) {
                    document.body.removeChild(overlay);
                    this.isLocked = false;
                } else {
                    window.location.href = '/login';
                }
            });
        }
    }


    disconnect() {
        ['touchmove', 'orientationchange', 'visibilitychange', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(event => {
            window.removeEventListener(event, this.onUserActivity);
        });
    }


}
