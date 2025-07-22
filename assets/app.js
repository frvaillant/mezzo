import './bootstrap.js';


import { createApp } from 'vue';

import Cashbox from "./components/Cashbox.vue";


function mountVueApp(selector, component, options = {}) {
    const element = document.querySelector(selector);
    if (!element) return;
    const app = createApp(component);

    app.mount(selector);
}

document.addEventListener('DOMContentLoaded', () => {
    mountVueApp('#cashbox', Cashbox);
});

import './index'

import './styles/tailwind.css';
import './styles/app.scss';
