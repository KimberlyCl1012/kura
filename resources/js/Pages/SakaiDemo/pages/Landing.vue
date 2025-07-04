<script setup lang="ts">
import FeaturesWidget from '../../../Components/Sakai/landing/FeaturesWidget.vue';
import FooterWidget from '../../../Components/Sakai/landing/FooterWidget.vue';
import HeroWidget from '../../../Components/Sakai/landing/HeroWidget.vue';
import HighlightsWidget from '../../../Components/Sakai/landing/HighlightsWidget.vue';
import PricingWidget from '../../../Components/Sakai/landing/PricingWidget.vue';
import TopbarWidget from '../../../Components/Sakai/landing/TopbarWidget.vue';
</script>

<template>
    <div class="bg-surface-0 dark:bg-surface-900">
        <div id="home" class="landing-wrapper overflow-hidden">
            <div class="py-6 px-6 mx-0 md:mx-12 lg:mx-20 lg:px-20 flex items-center justify-between relative lg:static">
                <TopbarWidget />
            </div>
            <HeroWidget />
            <FeaturesWidget />
            <HighlightsWidget />
            <PricingWidget />
            <FooterWidget />
        </div>
    </div>
</template>
