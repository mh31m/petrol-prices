import './bootstrap';

import { createApp } from 'vue';
import Dashboard from './components/dashboard.vue'
const app = createApp({
    components: {
        Dashboard,
    },
});

app.mount("#app");