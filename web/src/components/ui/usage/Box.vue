<script setup lang="ts">
import { CircleGauge } from "lucide-vue-next";
import { Box } from "@/components/ui/box";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid sm:grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Box class="w-70">
          <CircleGauge class="size-7 stroke-1 fill-foreground/10" />
          <div class="mt-6 text-2xl font-medium leading-8">$724,091.47</div>
          <div class="mt-1.5 text-xs uppercase opacity-70">Item Sales</div>
        </Box>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Box raised="single" class="w-70">
          <CircleGauge class="size-7 stroke-1 fill-foreground/10" />
          <div class="mt-6 text-2xl font-medium leading-8">$724,091.47</div>
          <div class="mt-1.5 text-xs uppercase opacity-70">Item Sales</div>
        </Box>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Box raised="double" class="w-70">
          <CircleGauge class="size-7 stroke-1 fill-foreground/10" />
          <div class="mt-6 text-2xl font-medium leading-8">$724,091.47</div>
          <div class="mt-1.5 text-xs uppercase opacity-70">Item Sales</div>
        </Box>
      </div>
    </div>
  </div>
</template>
