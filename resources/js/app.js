import './bootstrap'
import 'element-plus/dist/index.css'
import '../sass/app.scss'

const lockIOSAutoZoom = () => {
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent)
        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

    if (!isIOS) return;

    const content = 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover';
    let viewports = document.head.querySelectorAll('meta[name="viewport"]');

    if (!viewports.length) {
        const viewport = document.createElement('meta');
        viewport.name = 'viewport';
        document.head.appendChild(viewport);
        viewports = [viewport];
    }

    viewports.forEach((viewport) => {
        viewport.setAttribute('content', content);
    });
};

lockIOSAutoZoom();

new MutationObserver(lockIOSAutoZoom).observe(document.head, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['content'],
});

import { createApp } from 'vue'
import App from './components/App.vue'

import router from "./router"
import store from "./store"
import axios from './utils/repository'
import packageInfo from '../../package.json'
import ErrorLogger from './utils/ErrorLogger';

import { defineRule, configure } from 'vee-validate'
import { required, email, min, max } from '@vee-validate/rules'
import messages from './messages'

import AxiosMixin from '@/components/mixins/AxiosMixin.vue'
import AlertMixin from '@/components/mixins/AlertMixin.vue'
import UtilsMixin from '@/components/mixins/UtilsMixin.vue'

// Vee Validate rules
defineRule('required', required)
defineRule('email', email)
defineRule('min', min)
defineRule('max', max)

// Inicializar ErrorLogger globalmente
window.errorLogger = new ErrorLogger();

configure(messages)

const app = createApp(App)

// 🔥 AUTO IMPORT COMPONENTS (Vite way)
const modules = import.meta.glob('./**/*.vue', { eager: true })

Object.entries(modules).forEach(([path, module]) => {
    const componentName = path.split('/').pop().replace(/\.\w+$/, '')
    app.component(componentName, module.default)
})

// Mixins
app.mixin(AxiosMixin)
app.mixin(AlertMixin)
app.mixin(UtilsMixin)

// Global properties
app.config.globalProperties.$axios = axios;
app.config.globalProperties.$package = packageInfo;

// Configurar manejador de errores de Vue
app.config.errorHandler = (err, instance, info) => {
    window.errorLogger.logError({
        type: 'Vue Error',
        message: err.message,
        stack: err?.stack,
        componentName: instance?.$options?.name || instance?.$?.type?.name || 'Unknown',
        componentFile: instance?.$options?.__file,
        info: info,
        timestamp: new Date().toISOString(),
        url: window.location.href
    });
};

// Surface the real runtime error behind generic Vue warnings.
// app.config.errorHandler = (err, instance, info) => {
//     console.error('[Vue errorHandler]', {
//         message: err?.message,
//         stack: err?.stack,
//         component: instance?.type?.name,
//         info,
//     })
// }

// Use router & store
app.use(router)
app.use(store)

/**
 * Check if the current user has permission for a specific entity and action.
 *
 * Usage examples:
 *
 * 1. Basic permission check:
 *    ```js
 *    if ($can('projects', 'create')) {
 *      // User can create projects
 *    }
 *    ```
 *
 * 2. Permission with level restriction (integer condition):
 *    ```js
 *    // User must have a level <= 30
 *    $can('projects', 'view', 30)
 *    ```
 *
 * 3. Permission with level comparison (object condition):
 *    ```js
 *    // User must have a level greater than or equal to 30
 *    $can('projects', 'view', { gte: 30 })
 *    ```
 * 4. Permission with level comparison (object condition):
 *    ```js
 *    // User must have a level equal to 10 or 20
 *    $can('projects', 'view', { eq: [10, 20] })
 *    ```
 * 5. Permission with level comparison (object condition):
 *    ```js
 *    // User must have a level that is not equal to 30 and 40. The mode parameter specifies how to combine multiple values. By default, 'or' is used.
 *    $can('projects', 'view', { neq: [30, 40], mode: 'and' })
 *    ```
 *    ```js
 *    // User must have a level that is not equal to 25 or 26
 *    $can('projects', 'view', { neq: [25, 26], mode: 'or' })
 *    ```
 *
 * Supported comparison operators for the condition object:
 * - `lt`  → less than
 * - `lte` → less than or equal to
 * - `gt`  → greater than
 * - `gte` → greater than or equal to
 * - `eq`  → equal to
 * - `neq`  → not equal to
 *
 * @param {string} entity   - The entity name (e.g. 'projects', 'news').
 * @param {string} action   - The action name (e.g. 'view', 'create', 'delete').
 * @param {number|object} [condition] - Optional user level restriction.
 * @returns {boolean} Whether the user has permission.
 */
app.config.globalProperties.$can = (entity, action, condition = null) => {
    const can = store.getters['auth/hasPermission'](entity, action);
    const user = store.getters['auth/user'];
    if (!user) return false;

    const userLevel = user.level;

    const compareLevel = (userLevel, condition) => {
        if (condition == null) return true;
        if (typeof condition == 'number') return userLevel <= condition;

        if (typeof condition == 'object') {
            const mode = condition.mode == 'and' ? 'and' : 'or'; // default to OR
            const combine = (results) =>
                mode == 'and' ? results.every(Boolean) : results.some(Boolean);

            const check = (value, comparator) => {
                if (Array.isArray(value)) {
                    const results = value.map(v => comparator(userLevel, v));
                    return combine(results);
                }
                return comparator(userLevel, value);
            };

            if (condition.lt != undefined)
                return check(condition.lt, (a, b) => a < b);

            if (condition.lte != undefined)
                return check(condition.lte, (a, b) => a <= b);

            if (condition.gt != undefined)
                return check(condition.gt, (a, b) => a > b);

            if (condition.gte != undefined)
                return check(condition.gte, (a, b) => a >= b);

            if (condition.eq != undefined)
                return check(condition.eq, (a, b) => a == b);

            if (condition.neq != undefined)
                return check(condition.neq, (a, b) => a != b);
        }

        return true;
    };

    return can && compareLevel(userLevel, condition);
};
app.mount('#app')
