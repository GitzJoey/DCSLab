<script setup lang="ts">
import { Box } from '@/components/ui/box'
import { Lucide } from '@/components/ui/lucide'
import fakers from '@/utils/faker'

interface Props {
  class?: string
  boxClass?: string
}

const props = defineProps<Props>()
</script>

<template>
  <div
    v-bind="$attrs"
    :class="[
      'invisible opacity-0 scale-95 transition-all duration-200 delay-0 group-hover/profile:visible group-hover/profile:opacity-100 group-hover/profile:scale-100 group-hover/profile:delay-200',
      props.class,
    ]"
  >
    <Box
      :class="`text-foreground before:shadow-foreground/5 flex w-64 flex-col gap-2.5 px-6 py-5 before:rounded-2xl before:shadow-xl before:backdrop-blur after:rounded-2xl ${props.boxClass ?? ''}`"
    >
      <div class="flex flex-col gap-0.5">
        <div class="font-medium">{{ fakers[0]!['users'][0]!['name'] }}</div>
        <div class="mt-0.5 text-xs opacity-70">{{ fakers[0]!['jobs'][0] }}</div>
      </div>
      <div class="bg-foreground/5 h-px"></div>
      <div class="flex flex-col gap-0.5">
        <a class="hover:bg-foreground/5 -mx-3 flex gap-2.5 rounded-lg px-4 py-1.5" href="">
          <Lucide icon="Users" /> Profile
        </a>
        <a class="hover:bg-foreground/5 -mx-3 flex gap-2.5 rounded-lg px-4 py-1.5" href="">
          <Lucide icon="ShieldAlert" /> Add Account
        </a>
        <a class="hover:bg-foreground/5 -mx-3 flex gap-2.5 rounded-lg px-4 py-1.5" href="">
          <Lucide icon="FileLock" /> Reset Password
        </a>
        <a class="hover:bg-foreground/5 -mx-3 flex gap-2.5 rounded-lg px-4 py-1.5" href="">
          <Lucide icon="FileQuestion" /> Help
        </a>
      </div>
      <div class="bg-foreground/5 h-px"></div>
      <div class="flex flex-col gap-0.5">
        <a class="hover:bg-foreground/5 -mx-3 flex gap-2.5 rounded-lg px-4 py-1.5" href="">
          <Lucide icon="Power" /> Logout
        </a>
      </div>
    </Box>
  </div>
</template>
