<script setup lang="ts">
import { initializeMap, MapConfig } from "./google-map-loader";
import { HTMLAttributes, ref, onMounted } from "vue";

export type Init = (
  callback: (
    mapConfig: MapConfig
  ) => ReturnType<typeof initializeMap> | undefined
) => void;

interface GoogleMapLoaderProps extends HTMLAttributes {
  init: Init;
}

const props = defineProps<GoogleMapLoaderProps>();

const mapRef = ref<HTMLDivElement>();

onMounted(() => {
  props.init((mapConfig) => {
    if (mapRef.value) {
      return initializeMap(mapRef.value, mapConfig);
    }
  });
});
</script>

<template>
  <div ref="mapRef"></div>
</template>
