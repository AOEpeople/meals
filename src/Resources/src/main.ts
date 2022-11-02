import { createApp } from 'vue'
import router from './router'
import { createI18n } from 'vue-i18n'
import VueScreen from 'vue-screen'
import {Vue3ProgressPlugin} from '@marcoschulte/vue3-progress'

// Style
import '../style/output.css'
import '../scss/meals.scss'

// Vue Components
import App          from '@/App.vue'

// Translation
import en from '@/locales/en.json'
import de from '@/locales/de.json'

const i18n = createI18n({
    locale: navigator.language,
    fallbackLocale: 'en',
    fallbackWarn: false,
    missingWarn: false,
    messages: {
        en,
        de
    }
})

// Vue initialisation
const MainApp = createApp(App)
MainApp.config.performance = true // enable Vue Devtools
MainApp.use(i18n)
MainApp.use(router)
MainApp.use(VueScreen)
MainApp.use(Vue3ProgressPlugin)
MainApp.mount('#app')
