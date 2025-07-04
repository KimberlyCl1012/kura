<script setup>
import { Head } from "@inertiajs/vue3";
import { useLayout } from "./composables/layout";
import { computed, ref, watch, onUnmounted } from "vue";
import AppFooter from "./AppFooter.vue";
import AppSidebar from "./AppSidebar.vue";
import AppTopbar from "./AppTopbar.vue";

const { layoutConfig, layoutState, isSidebarActive } = useLayout();

const outsideClickListener = ref(null);

defineProps({
  title: String,
});

watch(isSidebarActive, (newVal) => {
  if (newVal) {
    bindOutsideClickListener();
  } else {
    unbindOutsideClickListener();
  }
});

onUnmounted(() => {
  unbindOutsideClickListener();
});

const containerClass = computed(() => {
  return {
    "layout-overlay": layoutConfig.menuMode === "overlay",
    "layout-static": layoutConfig.menuMode === "static",
    "layout-static-inactive":
      layoutState.staticMenuDesktopInactive && layoutConfig.menuMode === "static",
    "layout-overlay-active": layoutState.overlayMenuActive,
    "layout-mobile-active": layoutState.staticMenuMobileActive,
  };
});

function bindOutsideClickListener() {
  if (!outsideClickListener.value) {
    outsideClickListener.value = (event) => {
      if (isOutsideClicked(event)) {
        layoutState.overlayMenuActive = false;
        layoutState.staticMenuMobileActive = false;
        layoutState.menuHoverActive = false;
      }
    };
    document.addEventListener("click", outsideClickListener.value);
  }
}

function unbindOutsideClickListener() {
  if (outsideClickListener.value) {
    document.removeEventListener("click", outsideClickListener.value);
    outsideClickListener.value = null;
  }
}

function isOutsideClicked(event) {
  const sidebarEl = document.querySelector(".layout-sidebar");
  const topbarEl = document.querySelector(".layout-menu-button");

  return !(
    sidebarEl?.isSameNode(event.target) ||
    sidebarEl?.contains(event.target) ||
    topbarEl?.isSameNode(event.target) ||
    topbarEl?.contains(event.target)
  );
}
</script>

<template>
  <Head :title="title" />
  <div class="layout-wrapper" :class="containerClass">
    <AppTopbar />
    <AppSidebar />
    <div class="layout-main-container" >
      <div class="layout-main" >
        <div class="grid grid-cols-12 gap-8" id="dashboard">
          <div class="col-span-12 xl:col-span-12">
            <slot />
          </div>
        </div>
      </div>
      <AppFooter />
    </div>
    <div class="layout-mask animate-fadein"></div>
  </div>
  <Toast />
</template>
<style>
#dashboard {
  box-shadow: -2px 6px 22px -9px rgba(188, 188, 188, 0.75);
  -webkit-box-shadow: -2px 6px 22px -9px rgba(188, 188, 188, 0.75);
  -moz-box-shadow: -2px 6px 22px -9px rgba(188, 188, 188, 0.75);
  width: 100%;
  height: calc(100vh - 8rem);
  z-index: 999;
  overflow-y: auto;
  user-select: none;
  top: 6rem;
  left: 2rem;
  transition: transform var(--layout-section-transition-duration),
    left var(--layout-section-transition-duration);
  background-color: var(--surface-overlay);
  border-radius: var(--content-border-radius);
  padding: 0.5rem 1.5rem;
}
</style>
