<script lang="ts" setup>
import { Map } from "@/components/ui/map";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Map
          class="w-full h-96"
          :center="[13.388, 52.517]"
          :zoom="9.5"
          :markers="{
            type: 'FeatureCollection',
            features: [
              {
                type: 'Feature',
                properties: { name: 'Cafe Berlin', type: 'cafe' },
                geometry: { type: 'Point', coordinates: [13.388, 52.517] },
              },
              {
                type: 'Feature',
                properties: { name: 'Restaurant Alex', type: 'restaurant' },
                geometry: { type: 'Point', coordinates: [13.39, 52.518] },
              },
              {
                type: 'Feature',
                properties: { name: 'Coffee House', type: 'cafe' },
                geometry: { type: 'Point', coordinates: [13.385, 52.515] },
              },
              {
                type: 'Feature',
                properties: { name: 'Pizza Place', type: 'restaurant' },
                geometry: { type: 'Point', coordinates: [13.392, 52.519] },
              },
              {
                type: 'Feature',
                properties: { name: 'Burger Joint', type: 'restaurant' },
                geometry: { type: 'Point', coordinates: [13.387, 52.516] },
              },
              {
                type: 'Feature',
                properties: { name: 'Starbucks', type: 'cafe' },
                geometry: { type: 'Point', coordinates: [13.391, 52.52] },
              },
              {
                type: 'Feature',
                properties: { name: 'Sushi Bar', type: 'restaurant' },
                geometry: { type: 'Point', coordinates: [13.395, 52.522] },
              },
              {
                type: 'Feature',
                properties: { name: 'Bakery', type: 'cafe' },
                geometry: { type: 'Point', coordinates: [13.383, 52.514] },
              },
              {
                type: 'Feature',
                properties: { name: 'Italian Restaurant', type: 'restaurant' },
                geometry: { type: 'Point', coordinates: [13.398, 52.525] },
              },
              {
                type: 'Feature',
                properties: { name: 'Tea House', type: 'cafe' },
                geometry: { type: 'Point', coordinates: [13.38, 52.512] },
              },
            ],
          }"
        />
      </div>
    </div>
  </div>
</template>
