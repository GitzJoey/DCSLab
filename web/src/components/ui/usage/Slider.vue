<script lang="ts" setup>
import {
  SliderRoot,
  SliderLabel,
  SliderValueText,
  SliderControl,
  SliderTrack,
  SliderRange,
  SliderThumb,
  SliderHiddenInput,
  SliderMarkerGroup,
  SliderMarker,
} from "@/components/ui/slider";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <div class="text-sm text-muted-foreground">
          <SliderRoot class="w-72" :defaultValue="[20]">
            <SliderLabel>Max Items</SliderLabel>
            <SliderControl>
              <SliderTrack>
                <SliderRange />
              </SliderTrack>
              <SliderThumb :index="0">
                <SliderHiddenInput />
              </SliderThumb>
            </SliderControl>
            <div
              class="flex items-center text-xs gap-1 font-medium justify-center opacity-70"
            >
              <SliderValueText /> Items
            </div>
          </SliderRoot>
        </div>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <div class="text-sm text-muted-foreground">
          <SliderRoot class="w-72" :value="[20, 80]">
            <SliderLabel>Price Range</SliderLabel>
            <SliderControl>
              <SliderTrack>
                <SliderRange />
              </SliderTrack>
              <SliderThumb :index="0">
                <SliderHiddenInput />
              </SliderThumb>
              <SliderThumb :index="1">
                <SliderHiddenInput />
              </SliderThumb>
            </SliderControl>
            <SliderMarkerGroup>
              <SliderMarker :value="0">$0</SliderMarker>
              <SliderMarker :value="25">$25</SliderMarker>
              <SliderMarker :value="50">$50</SliderMarker>
              <SliderMarker :value="75">$75</SliderMarker>
              <SliderMarker :value="100">$100</SliderMarker>
            </SliderMarkerGroup>
          </SliderRoot>
        </div>
      </div>
    </div>
  </div>
</template>
