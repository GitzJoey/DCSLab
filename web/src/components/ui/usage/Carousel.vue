<script lang="ts" setup>
import {
  CarouselRoot,
  CarouselControl,
  CarouselPrevTrigger,
  CarouselNextTrigger,
  CarouselIndicatorGroup,
  CarouselIndicator,
  CarouselItemGroup,
  CarouselItem,
} from "@/components/ui/carousel";

const images = Array.from(
  { length: 5 },
  (_, i) => `https://picsum.photos/seed/${i + 1}/500/300`
);
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <CarouselRoot
          :default-page="0"
          :slide-count="images.length"
          class="size-72"
        >
          <CarouselControl>
            <CarouselPrevTrigger />
            <CarouselNextTrigger />
          </CarouselControl>
          <CarouselIndicatorGroup>
            <CarouselIndicator
              v-for="(_, index) in images"
              :key="index"
              :index="index"
            />
          </CarouselIndicatorGroup>
          <CarouselItemGroup>
            <CarouselItem
              v-for="(image, index) in images"
              :key="index"
              :index="index"
              class="text-5xl bold flex items-center justify-center"
            >
              {{ index + 1 }}
            </CarouselItem>
          </CarouselItemGroup>
        </CarouselRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <CarouselRoot
          :default-page="0"
          :slide-count="images.length"
          class="size-72"
        >
          <CarouselControl>
            <CarouselPrevTrigger />
            <CarouselNextTrigger />
          </CarouselControl>
          <CarouselIndicatorGroup>
            <CarouselIndicator
              v-for="(_, index) in images"
              :key="index"
              :index="index"
            />
          </CarouselIndicatorGroup>
          <CarouselItemGroup>
            <CarouselItem
              v-for="(image, index) in images"
              :key="index"
              :index="index"
            >
              <img :src="image" :alt="`Slide ${index}`" />
            </CarouselItem>
          </CarouselItemGroup>
        </CarouselRoot>
      </div>
    </div>
  </div>
</template>
