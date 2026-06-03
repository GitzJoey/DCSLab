<script lang="ts" setup>
import { SwitchRoot, SwitchControl, SwitchLabel } from "@/components/ui/switch";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap">
        <SwitchRoot>
          <SwitchControl />
          <SwitchLabel>Airplane Mode</SwitchLabel>
        </SwitchRoot>
      </div>
    </div>
  </div>
</template>
