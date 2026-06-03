<script lang="ts" setup>
import {
  ProgressRoot,
  ProgressLabel,
  ProgressValueText,
  ProgressTrack,
  ProgressRange,
} from "@/components/ui/progress-linear";
</script>

<template>
  <ProgressRoot :defaultValue="42">
    <ProgressLabel>Progress Linear</ProgressLabel>
    <ProgressTrack class="max-w-72">
      <ProgressRange />
    </ProgressTrack>
    <ProgressValueText />
  </ProgressRoot>
</template>
