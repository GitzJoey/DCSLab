<script setup lang="ts">
import { computed } from "vue";
import { useUserContextStore } from "../../stores/user-context";
import _ from "lodash";

const userContextStore = useUserContextStore();
const userContext = computed(() => userContextStore.userContext);

const welcomeName = computed(() => userContext.value.profile.first_name);
</script>

<template>
  <div class="col-span-12 mt-8">
    <div class="flex items-center h-10 intro-y">
      <h2 class="mr-5 text-lg font-medium truncate">
        Welcome, {{ welcomeName }}
      </h2>
      <a href="" class="flex items-center ml-auto text-primary">
        <Lucide icon="RefreshCcw" class="w-4 h-4 mr-3" />
      </a>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
      <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
        <div
          :class="[
            'relative zoom-in',
            'before:box before:absolute before:inset-x-3 before:mt-3 before:h-full before:bg-slate-50 before:content-[\'\']',
          ]"
        >
          <div class="p-5 box">
            <div class="flex">
              <Lucide
                icon="ShoppingCart"
                class="w-[28px] h-[28px] text-primary"
              />
              <div class="ml-auto">
                <Tippy
                  as="div"
                  class="cursor-pointer bg-success py-[3px] flex rounded-full text-white text-xs pl-2 pr-1 items-center font-medium"
                  content=""
                >
                  &nbsp;
                  <Lucide icon="ChevronUp" class="w-4 h-4 ml-0.5" />
                </Tippy>
              </div>
            </div>
            <div class="mt-6 text-3xl font-medium leading-8"></div>
            <div class="mt-1 text-base text-slate-500"></div>
          </div>
        </div>
      </div>
      <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
        <div
          :class="[
            'relative zoom-in',
            'before:box before:absolute before:inset-x-3 before:mt-3 before:h-full before:bg-slate-50 before:content-[\'\']',
          ]"
        >
          <div class="p-5 box">
            <div class="flex">
              <Lucide
                icon="CreditCard"
                class="w-[28px] h-[28px] text-pending"
              />
              <div class="ml-auto">
                <Tippy
                  as="div"
                  class="cursor-pointer bg-danger py-[3px] flex rounded-full text-white text-xs pl-2 pr-1 items-center font-medium"
                  content=""
                >
                  &nbsp;
                  <Lucide icon="ChevronDown" class="w-4 h-4 ml-0.5" />
                </Tippy>
              </div>
            </div>
            <div class="mt-6 text-3xl font-medium leading-8"></div>
            <div class="mt-1 text-base text-slate-500"></div>
          </div>
        </div>
      </div>
      <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
        <div
          :class="[
            'relative zoom-in',
            'before:box before:absolute before:inset-x-3 before:mt-3 before:h-full before:bg-slate-50 before:content-[\'\']',
          ]"
        >
          <div class="p-5 box">
            <div class="flex">
              <Lucide icon="Monitor" class="w-[28px] h-[28px] text-warning" />
              <div class="ml-auto">
                <Tippy
                  as="div"
                  class="cursor-pointer bg-success py-[3px] flex rounded-full text-white text-xs pl-2 pr-1 items-center font-medium"
                  content=""
                >
                  &nbsp;
                  <Lucide icon="ChevronUp" class="w-4 h-4 ml-0.5" />
                </Tippy>
              </div>
            </div>
            <div class="mt-6 text-3xl font-medium leading-8"></div>
            <div class="mt-1 text-base text-slate-500"></div>
          </div>
        </div>
      </div>
      <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
        <div
          :class="[
            'relative zoom-in',
            'before:box before:absolute before:inset-x-3 before:mt-3 before:h-full before:bg-slate-50 before:content-[\'\']',
          ]"
        >
          <div class="p-5 box">
            <div class="flex">
              <Lucide icon="User" class="w-[28px] h-[28px] text-success" />
              <div class="ml-auto">
                <Tippy
                  as="div"
                  class="cursor-pointer bg-success py-[3px] flex rounded-full text-white text-xs pl-2 pr-1 items-center font-medium"
                  content=""
                >
                  &nbsp;
                  <Lucide icon="ChevronUp" class="w-4 h-4 ml-0.5" />
                </Tippy>
              </div>
            </div>
            <div class="mt-6 text-3xl font-medium leading-8"></div>
            <div class="mt-1 text-base text-slate-500"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
