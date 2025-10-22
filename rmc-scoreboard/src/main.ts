import '@/assets/main.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';

import App from './App.vue';
import router from './router';

import { OhVueIcon, addIcons } from 'oh-vue-icons';
import { FaTrophy, FaChevronDown, FaSpinner, FaCheck, FaPlayCircle } from 'oh-vue-icons/icons/fa';
addIcons(FaTrophy, FaChevronDown, FaSpinner, FaCheck, FaPlayCircle);

const app = createApp(App);

app.directive('visible', function(el, binding) {
    el.style.visibility = binding.value ? 'visible' : 'hidden';
});

app.component('v-icon', OhVueIcon);
app.use(createPinia());
app.use(router);

app.mount('#app');
