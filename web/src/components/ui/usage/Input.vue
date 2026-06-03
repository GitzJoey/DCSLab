<script lang="ts" setup>
import { Input } from "@/components/ui/input";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Input type="email" placeholder="Email" />
      </div>
    </div>
  </div>
</template>
