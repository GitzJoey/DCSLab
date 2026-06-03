<script lang="ts" setup>
import { Textarea } from "@/components/ui/textarea";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap">
        <Textarea placeholder="Type your message here." />
      </div>
    </div>
  </div>
</template>
