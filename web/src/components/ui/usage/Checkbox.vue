<script lang="ts" setup>
import {
  CheckboxRoot,
  CheckboxLabel,
  CheckboxControl,
} from "@/components/ui/checkbox";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <CheckboxRoot>
          <CheckboxControl />
          <CheckboxLabel>Accept terms and conditions</CheckboxLabel>
        </CheckboxRoot>
      </div>
    </div>
  </div>
</template>
