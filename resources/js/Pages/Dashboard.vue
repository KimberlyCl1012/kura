<script setup>
import AppLayout from "../Layouts/Sakai/AppLayout.vue";
import { PhotoService } from "../Services/PhotoService.js";
import { ProductService } from "../Services/ProductService.js";
import { onMounted, ref } from "vue";
import Tag from "primevue/tag";
import Carousel from "primevue/carousel";

const products = ref([]);
const images = ref([]);
const galleriaResponsiveOptions = ref([
    {
        breakpoint: "1024px",
        numVisible: 5,
    },
    {
        breakpoint: "960px",
        numVisible: 4,
    },
    {
        breakpoint: "768px",
        numVisible: 3,
    },
    {
        breakpoint: "560px",
        numVisible: 1,
    },
]);
const carouselResponsiveOptions = ref([
    {
        breakpoint: "1024px",
        numVisible: 3,
        numScroll: 3,
    },
    {
        breakpoint: "768px",
        numVisible: 2,
        numScroll: 2,
    },
    {
        breakpoint: "560px",
        numVisible: 1,
        numScroll: 1,
    },
]);

onMounted(() => {
    ProductService.getProductsSmall().then((data) => (products.value = data));
    PhotoService.getImages().then((data) => (images.value = data));
});

function getSeverity(status) {
    switch (status) {
        case "INSTOCK":
            return "success";

        case "LOWSTOCK":
            return "warning";

        case "OUTOFSTOCK":
            return "danger";

        default:
            return null;
    }
}

// GUIA DE UTILIDADES SAKAI
// import BestSellingWidget from '../components/Sakai/dashboard/BestSellingWidget.vue';
// import NotificationsWidget from '../components/Sakai/dashboard/NotificationsWidget.vue';
// import RecentSalesWidget from '../components/Sakai/dashboard/RecentSalesWidget.vue';
// import RevenueStreamWidget from '../components/Sakai/dashboard/RevenueStreamWidget.vue';
// import StatsWidget from '../components/Sakai/dashboard/StatsWidget.vue';
//END GUIA UTILIDADES
</script>

<template>
    <AppLayout title="Dashboard">
        <!-- GUIA DE UTILIDADES SAKAI -->
        <!-- <div class="grid grid-cols-12 gap-8">
      <StatsWidget />

      <div class="col-span-12 xl:col-span-6">
        <RecentSalesWidget />
        <BestSellingWidget />
      </div>
      <div class="col-span-12 xl:col-span-6">
        <RevenueStreamWidget />
        <NotificationsWidget />
      </div>
    </div> -->
        <!-- END GUIA DE UTILIDADES SAKAI -->

        <div class="card">
            <div class="font-semibold text-xl mb-4">Bienvenidos</div>
            <div class="flex items-center justify-center bg-surface-0 dark:bg-surface-950 p-5">
                <img src="../../img/logos/red/kura_1.svg" alt="Procomsa Logo"
                    class="w-2/6 max-w-sm rounded-md mx-auto" />
            </div>
            <div class="container">
                <p class="text-center mb-3">
                   <b>Clínica avanzada de heridas: </b> "Un servicio muy profesional, con delicadeza y efectividad."
                </p>
            </div>
            <Carousel :value="products" :numVisible="3" :numScroll="3" :responsiveOptions="carouselResponsiveOptions">
                <template #item="slotProps">
                    <div class="border border-surface-200 dark:border-surface-700 rounded m-2 p-4">
                        <div class="mb-4">
                            <div class="relative mx-auto">
                                <img :src="'https://kuratrackerpro.procomsa.mx/img/gallery/' +
                                    slotProps.data.image
                                    " :alt="slotProps.data.name" class="w-full rounded" />
                                <!-- <div class="dark:bg-surface-900 absolute rounded-border" style="left: 5px; top: 5px">
                                    <Tag :value="slotProps.data.inventoryStatus"
                                        :severity="getSeverity(slotProps.data.inventoryStatus)" />
                                </div> -->
                            </div>
                        </div>
                    </div>
                </template>
            </Carousel>
        </div>
    </AppLayout>
</template>
