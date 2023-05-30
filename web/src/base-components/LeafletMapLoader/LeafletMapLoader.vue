<script setup lang="ts">
import { initializeMap, MapConfig, LeafletElement } from "./leaflet-map-loader";
import { HTMLAttributes, ref, onMounted } from "vue";

export type Init = (
  callback: (
    mapConfig: MapConfig
  ) => ReturnType<typeof initializeMap> | undefined
) => void;

interface LeafletMapLoaderProps extends /* @vue-ignore */ HTMLAttributes {
  init: Init;
  darkMode?: boolean;
}

const props = defineProps<LeafletMapLoaderProps>();

const mapRef = ref<LeafletElement>();

onMounted(() => {
  props.init((mapConfig) => {
    if (mapRef.value) {
      return initializeMap(mapRef.value, mapConfig);
    }
  });
});
</script>

<template>
  <div
    :class="{
      '[&_.leaflet-tile-pane]:saturate-[.3]': !props.darkMode,
      '[&_.leaflet-tile-pane]:grayscale [&_.leaflet-tile-pane]:invert [&_.leaflet-tile-pane]:brightness-90 [&_.leaflet-tile-pane]:hue-rotate-15':
        props.darkMode,
    }"
  >
    <div ref="mapRef" class="w-full h-full"></div>
  </div>
</template>
