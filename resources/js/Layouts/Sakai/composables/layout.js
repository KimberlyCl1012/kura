// composables/layout.js
import { computed, reactive, watch, onMounted } from 'vue';

const layoutConfig = reactive({
    preset: 'Aura',
    primary: localStorage.getItem('layout-primary') || 'emerald',
    surface: null,
    darkTheme: localStorage.getItem('dark-theme') === 'true',
    menuMode: 'static'
});

const layoutState = reactive({
    staticMenuDesktopInactive: false,
    overlayMenuActive: false,
    profileSidebarVisible: false,
    configSidebarVisible: false,
    staticMenuMobileActive: false,
    menuHoverActive: false,
    activeMenuItem: null
});

document.documentElement.classList.toggle('app-dark', layoutConfig.darkTheme);

function applyPrimaryColor(color) {
    document.documentElement.style.setProperty('--primary-color', `var(--${color}-500)`);
}
applyPrimaryColor(layoutConfig.primary);

export function useLayout() {
    const setActiveMenuItem = (item) => {
        layoutState.activeMenuItem = item.value || item;
    };

    const toggleDarkMode = () => {
        if (!document.startViewTransition) {
            executeDarkModeToggle();
            return;
        }
        document.startViewTransition(() => executeDarkModeToggle());
    };

    const executeDarkModeToggle = () => {
        layoutConfig.darkTheme = !layoutConfig.darkTheme;
        localStorage.setItem('dark-theme', layoutConfig.darkTheme);
        document.documentElement.classList.toggle('app-dark', layoutConfig.darkTheme);
    };

    const toggleMenu = () => {
        if (layoutConfig.menuMode === 'overlay') {
            layoutState.overlayMenuActive = !layoutState.overlayMenuActive;
        }

        if (window.innerWidth > 991) {
            layoutState.staticMenuDesktopInactive = !layoutState.staticMenuDesktopInactive;
        } else {
            layoutState.staticMenuMobileActive = !layoutState.staticMenuMobileActive;
        }
    };

    const changePrimaryColor = (color) => {
        layoutConfig.primary = color;
        localStorage.setItem('layout-primary', color);
        applyPrimaryColor(color);
    };

    const isSidebarActive = computed(() => layoutState.overlayMenuActive || layoutState.staticMenuMobileActive);
    const isDarkTheme = computed(() => layoutConfig.darkTheme);
    const getPrimary = computed(() => layoutConfig.primary);
    const getSurface = computed(() => layoutConfig.surface);

    return {
        layoutConfig,
        layoutState,
        toggleMenu,
        isSidebarActive,
        isDarkTheme,
        getPrimary,
        getSurface,
        setActiveMenuItem,
        toggleDarkMode,
        changePrimaryColor
    };
}
