<script lang="ts" setup>
import {
  ProgressRoot,
  ProgressLabel,
  ProgressValueText,
  ProgressCircle,
  ProgressCircleTrack,
  ProgressCircleRange,
} from "@/components/ui/progress-circular";
</script>

<template>
  <ProgressRoot :defaultValue="42">
    <ProgressLabel>Progress Circular</ProgressLabel>
    <ProgressCircle class="max-w-48">
      <ProgressCircleTrack />
      <ProgressCircleRange />
    </ProgressCircle>
    <ProgressValueText />
  </ProgressRoot>
</template>
