<script setup lang="ts">
import { Lucide } from '@/components/ui/lucide'
import { Input } from '@/components/ui/input'
import { DialogRoot, DialogContent } from '@/components/ui/dialog'
import { AvatarRoot, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import fakers from '@/utils/faker'

const { open } = defineProps<{
  open?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
}>()
</script>

<template>
  <DialogRoot :open="open" @openChange="(details) => emit('update:open', details.open)">
    <DialogContent class="sm:max-w-2xl p-0">
      <div class="relative border-b border-dashed border-foreground/15">
        <Lucide class="absolute inset-y-0 my-auto ml-4 opacity-70 z-50" icon="Search" />
        <Input
          class="w-full pl-12 bg-transparent border-none pr-19 h-14 focus:ring-offset-transparent focus-visible:ring-transparent shadow-none"
          type="text"
          placeholder="Quick search..."
        />
        <div
          class="absolute inset-y-0 right-0 flex items-center h-6 px-2 my-auto mr-4 border rounded-lg border-foreground/30 text-xs opacity-70"
        >
          esc
        </div>
      </div>
      <div class="flex flex-wrap gap-2 px-5 mt-4">
        <a
          class="hover:bg-foreground/5 flex items-center gap-x-1.5 rounded-full border border-foreground/15 px-3 py-0.5 text-xs font-medium"
          href=""
        >
          <Lucide class="size-4" icon="Users2" />
          Users
        </a>
        <a
          class="hover:bg-foreground/5 flex items-center gap-x-1.5 rounded-full border border-foreground/15 px-3 py-0.5 text-xs font-medium"
          href=""
        >
          <Lucide class="size-4" icon="Building2" />
          Departments
        </a>
        <a
          class="hover:bg-foreground/5 flex items-center gap-x-1.5 rounded-full border border-foreground/15 px-3 py-0.5 text-xs font-medium"
          href=""
        >
          <Lucide class="size-4" icon="KanbanSquare" />
          Products
        </a>
        <a
          class="hover:bg-foreground/5 flex items-center gap-x-1.5 rounded-full border border-foreground/15 px-3 py-0.5 text-xs font-medium"
          href=""
        >
          <Lucide class="size-4" icon="MailCheck" />
          Mails
        </a>
      </div>
      <div class="px-5 py-4 mt-2">
        <div class="flex items-center">
          <div class="text-xs uppercase opacity-70">Users</div>
          <a class="ml-auto text-xs opacity-70" href=""> See All </a>
        </div>
        <div class="mt-3.5 flex flex-col gap-1">
          <template v-for="(faker, fakerKey) in fakers.slice(0, 3)" :key="fakerKey">
            <a
              class="hover:border-foreground/10 hover:bg-foreground/5 -mx-1 flex items-center gap-2.5 rounded-xl border border-transparent p-1"
              href=""
            >
              <AvatarRoot class="size-8">
                <AvatarFallback>{{ faker.users[0]?.name.charAt(0) }}</AvatarFallback>
                <AvatarImage :src="faker.photos[2]" />
              </AvatarRoot>
              <div class="font-medium truncate text-sm">
                {{ faker.users[0]?.name }}
              </div>
              <div class="hidden opacity-70 sm:block text-xs">
                {{ faker.jobs[0] }}
              </div>
            </a>
          </template>
        </div>
      </div>
      <div class="px-5 py-4 border-t border-dashed border-foreground/15">
        <div class="flex items-center">
          <div class="text-xs uppercase opacity-70">Departments</div>
          <a class="ml-auto text-xs opacity-70" href=""> See All </a>
        </div>
        <div class="mt-3.5 flex flex-col gap-1">
          <template v-for="(faker, fakerKey) in fakers.slice(0, 3)" :key="fakerKey">
            <a
              class="hover:border-foreground/10 hover:bg-foreground/5 -mx-1 flex items-center gap-2.5 rounded-xl border border-transparent p-1"
              href=""
            >
              <div
                class="flex items-center justify-center overflow-hidden border rounded-lg border-primary/10 bg-primary/10 text-primary size-8"
              >
                <Lucide v-if="fakerKey % 2 === 0" class="stroke-1" icon="Store" />
                <Lucide v-else class="stroke-1" icon="Hotel" />
              </div>
              <div class="font-medium truncate text-sm">
                {{ faker.products[0]?.name }}
              </div>
              <div class="hidden opacity-70 sm:block text-xs">
                {{ faker.products[0]?.category }}
              </div>
            </a>
          </template>
        </div>
      </div>
      <div class="px-5 py-4 border-t border-dashed border-foreground/15 pb-6">
        <div class="flex items-center">
          <div class="text-xs uppercase opacity-70">Products</div>
          <a class="ml-auto text-xs opacity-70" href=""> See All </a>
        </div>
        <div class="mt-3.5 flex flex-col gap-1">
          <template v-for="(faker, fakerKey) in fakers.slice(0, 3)" :key="fakerKey">
            <a
              class="hover:border-foreground/10 hover:bg-foreground/5 -mx-1 flex items-center gap-2.5 rounded-xl border border-transparent p-1"
              href=""
            >
              <AvatarRoot class="size-8 rounded-lg overflow-hidden">
                <AvatarFallback>{{ faker.foods[0]?.name.charAt(0) }}</AvatarFallback>
                <AvatarImage :src="faker.images[2]" />
              </AvatarRoot>
              <div class="font-medium truncate text-sm">
                {{ faker.foods[0]?.name }}
              </div>
              <div class="hidden opacity-70 sm:block text-xs">
                {{ faker.products[1]?.category }}
              </div>
            </a>
          </template>
        </div>
      </div>
    </DialogContent>
  </DialogRoot>
</template>
