<script lang="ts" setup>
import {
  AvatarRoot,
  AvatarFallback,
  AvatarImage,
} from "@/components/ui/avatar";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap">
        <AvatarRoot>
          <AvatarFallback>PA</AvatarFallback>
          <AvatarImage src="https://i.pravatar.cc/300" alt="avatar" />
        </AvatarRoot>
      </div>
      <div class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap">
        <AvatarRoot :bordered="false">
          <AvatarFallback>PA</AvatarFallback>
          <AvatarImage src="https://i.pravatar.cc/300" alt="avatar" />
        </AvatarRoot>
      </div>
      <div class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap">
        <AvatarRoot class="rounded-full">
          <AvatarFallback>PA</AvatarFallback>
          <AvatarImage src="https://i.pravatar.cc/300" alt="avatar" />
        </AvatarRoot>
      </div>
      <div class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap">
        <AvatarRoot class="rounded-full" :bordered="false">
          <AvatarFallback>PA</AvatarFallback>
          <AvatarImage src="https://i.pravatar.cc/300" alt="avatar" />
        </AvatarRoot>
      </div>
    </div>
  </div>
</template>
