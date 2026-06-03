<script setup lang="ts">
import { Box } from '@/components/ui/box'
import { Lucide } from '@/components/ui/lucide'
import { AvatarRoot, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import fakers from '@/utils/faker'
import _ from 'lodash'

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
      'invisible opacity-0 scale-95 transition-all duration-200 delay-0 group-hover/notifications:visible group-hover/notifications:opacity-100 group-hover/notifications:scale-100 group-hover/notifications:delay-200',
      props.class,
    ]"
  >
    <Box
      :class="`before:shadow-foreground/5 z-50 flex w-96 flex-col gap-2.5 px-6 py-5 before:rounded-2xl before:shadow-xl before:backdrop-blur after:rounded-2xl ${props.boxClass ?? ''}`"
    >
      <div class="flex place-content-between items-center">
        <div class="font-medium">Notifications</div>
        <a class="text-primary" href="">View More</a>
      </div>
      <div class="mt-1 flex flex-col gap-2.5">
        <a
          v-for="(faker, fakerKey) in _.take(fakers, 5)"
          :key="fakerKey"
          class="hover:border-foreground/10 hover:bg-foreground/5 -mx-2 flex items-center gap-3.5 rounded-2xl border border-transparent p-2"
          href=""
        >
          <AvatarRoot class="size-11 flex-none rounded-full">
            <AvatarFallback>PA</AvatarFallback>
            <AvatarImage :src="faker['photos'][2]" alt="avatar" />
          </AvatarRoot>
          <div class="flex flex-col gap-1">
            <div class="flex place-content-between items-center">
              <div class="font-medium">{{ faker['users'][0]!['name'] }}</div>
              <div class="text-xs opacity-70">{{ faker['times'][0] }}</div>
            </div>
            <div class="line-clamp-2 opacity-70">
              {{ faker['news'][0]!['shortContent'] }}
            </div>
          </div>
        </a>
      </div>
    </Box>
  </div>
</template>
