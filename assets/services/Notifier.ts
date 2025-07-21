import {Notyf} from "notyf";

export default class Notifier {
    private notyf: Notyf;


    constructor() {

        this.notyf = new Notyf({
            duration: 3000,
            position: {
                x: 'right',
                y: 'top',
            },
            types: [
                {
                    type: 'info',
                    background: 'blue',
                    icon: false,
                    duration: 3000,
                    dismissible: true,
                },

                {
                    type: 'warning',
                    background: 'orange',
                    duration: 3000,
                    dismissible: true,
                    icon: {
                        className: 'material-icons',
                        tagName: 'i',
                        text: 'warning'
                    }
                },
                {
                    type: 'error',
                    background: 'indianred',
                    duration: 3000,
                    dismissible: true
                },
                {
                    type: 'success',
                    background: '#00cc99',
                    duration: 3000,
                    dismissible: true
                }
            ]
        });
    }

    success = (msg: string) => {
        this.notyf.success(msg)
    }

    info = (msg: string) => {
        this.notyf.open({ type: 'info', message: msg })
    }

    warning = (msg: string) => {
        this.notyf.open({ type: 'warning', message: msg })
    }

    error = (msg: string) => {
        this.notyf.error(msg)
    }

}
