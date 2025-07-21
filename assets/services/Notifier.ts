import {Notyf} from "notyf";

class Notifier {
    private static notyf = new Notyf({
        duration: 3000,
        position: { x: 'right', y: 'top' },
        types: [
            {
                type: 'info',
                background: 'blue',
                icon: false,
                duration: 3000,
                dismissible: true,
                className: 'font-bold text-xl',
            },
            {
                type: 'warning',
                background: 'orange',
                duration: 3000,
                dismissible: true,
                className: 'font-bold text-xl',
                icon: {
                    className: 'material-icons',
                    tagName: 'i',
                    text: 'warning',
                },
            },
            {
                type: 'error',
                background: 'indianred',
                duration: 3000,
                dismissible: true,
                className: 'font-bold text-xl',
            },
            {
                type: 'success',
                background: '#00cc99',
                duration: 3000,
                dismissible: true,
                className: 'font-bold text-xl',
            },
        ],
    });

    static success(msg: string) {
        this.notyf.success(msg);
    }

    static info(msg: string) {
        this.notyf.open({ type: 'info', message: msg });
    }

    static warning(msg: string) {
        this.notyf.open({ type: 'warning', message: msg });
    }

    static error(msg: string) {
        this.notyf.error(msg);
    }
}

export default Notifier;
