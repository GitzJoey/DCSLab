<script setup lang="ts">
import { ref } from "vue";
import Lucide from "../../base-components/Lucide";
import logoUrl from "../../assets/images/logo.svg";
import Breadcrumb from "../../base-components/Breadcrumb";
import { FormInput } from "../../base-components/Form";
import { Menu, Popover } from "../../base-components/Headless";
import fakerData from "../../utils/faker";
import _ from "lodash";
import { TransitionRoot } from "@headlessui/vue";

const props = defineProps<{
  layout?: "side-menu" | "simple-menu" | "top-menu";
}>();

const searchDropdown = ref(false);
const showSearchDropdown = () => {
  searchDropdown.value = true;
};
const hideSearchDropdown = () => {
  searchDropdown.value = false;
};
</script>

<template>
  <div
    :class="[
      'h-[70px] md:h-[65px] z-[51] border-b border-white/[0.08] mt-12 md:mt-0 -mx-3 sm:-mx-8 md:-mx-0 px-3 md:border-b-0 relative md:fixed md:inset-x-0 md:top-0 sm:px-8 md:px-10 md:pt-10 md:bg-gradient-to-b md:from-slate-100 md:to-transparent dark:md:from-darkmode-700',
      props.layout == 'top-menu' && 'dark:md:from-darkmode-800',
      'before:content-[\'\'] before:absolute before:h-[65px] before:inset-0 before:top-0 before:mx-7 before:bg-primary/30 before:mt-3 before:rounded-xl before:hidden before:md:block before:dark:bg-darkmode-600/30',
      'after:content-[\'\'] after:absolute after:inset-0 after:h-[65px] after:mx-3 after:bg-primary after:mt-5 after:rounded-xl after:shadow-md after:hidden after:md:block after:dark:bg-darkmode-600',
    ]"
  >
    <div class="flex items-center h-full">
      <!-- BEGIN: Logo -->
      <RouterLink
        :to="{ name: 'side-menu-dashboard-overview-1' }"
        :class="[
          '-intro-x hidden md:flex',
          props.layout == 'side-menu' && 'xl:w-[180px]',
          props.layout == 'simple-menu' && 'xl:w-auto',
          props.layout == 'top-menu' && 'w-auto',
        ]"
      >
        <img
          alt="Enigma Tailwind HTML Admin Template"
          class="w-6"
          :src="logoUrl"
        />
        <span
          :class="[
            'ml-3 text-lg text-white',
            props.layout == 'side-menu' && 'hidden xl:block',
            props.layout == 'simple-menu' && 'hidden',
          ]"
        >
          Enigma
        </span>
      </RouterLink>
      <!-- END: Logo -->
      <!-- BEGIN: Breadcrumb -->
      <Breadcrumb
        light
        :class="[
          'h-[45px] md:ml-10 md:border-l border-white/[0.08] dark:border-white/[0.08] mr-auto -intro-x',
          props.layout != 'top-menu' && 'md:pl-6',
          props.layout == 'top-menu' && 'md:pl-10',
        ]"
      >
        <Breadcrumb.Link to="/">Application</Breadcrumb.Link>
        <Breadcrumb.Link to="/" :active="true"> Dashboard </Breadcrumb.Link>
      </Breadcrumb>
      <!-- END: Breadcrumb -->
      <!-- BEGIN: Search -->
      <div class="relative mr-3 intro-x sm:mr-6">
        <div class="relative hidden sm:block">
          <FormInput
            type="text"
            class="border-transparent w-56 shadow-none rounded-full bg-slate-200 pr-8 transition-[width] duration-300 ease-in-out focus:border-transparent focus:w-72 dark:bg-darkmode-400"
            placeholder="Search..."
            @focus="showSearchDropdown"
            @blur="hideSearchDropdown"
          />
          <Lucide
            icon="Search"
            class="absolute inset-y-0 right-0 w-5 h-5 my-auto mr-3 text-slate-600 dark:text-slate-500"
          />
        </div>
        <a class="relative text-white/70 sm:hidden" href="">
          <Lucide icon="Search" class="w-5 h-5 dark:text-slate-500" />
        </a>
        <TransitionRoot
          as="template"
          :show="searchDropdown"
          enter="transition-all ease-linear duration-150"
          enterFrom="mt-5 invisible opacity-0 translate-y-1"
          enterTo="mt-[3px] visible opacity-100 translate-y-0"
          entered="mt-[3px]"
          leave="transition-all ease-linear duration-150"
          leaveFrom="mt-[3px] visible opacity-100 translate-y-0"
          leaveTo="mt-5 invisible opacity-0 translate-y-1"
        >
          <div class="absolute right-0 z-10 mt-[3px]">
            <div class="w-[450px] p-5 box">
              <div class="mb-2 font-medium">Pages</div>
              <div class="mb-5">
                <a href="" class="flex items-center">
                  <div
                    class="flex items-center justify-center w-8 h-8 rounded-full bg-success/20 dark:bg-success/10 text-success"
                  >
                    <Lucide icon="Inbox" class="w-4 h-4" />
                  </div>
                  <div class="ml-3">Mail Settings</div>
                </a>
                <a href="" class="flex items-center mt-2">
                  <div
                    class="flex items-center justify-center w-8 h-8 rounded-full bg-pending/10 text-pending"
                  >
                    <Lucide icon="Users" class="w-4 h-4" />
                  </div>
                  <div class="ml-3">Users & Permissions</div>
                </a>
                <a href="" class="flex items-center mt-2">
                  <div
                    class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 dark:bg-primary/20 text-primary/80"
                  >
                    <Lucide icon="CreditCard" class="w-4 h-4" />
                  </div>
                  <div class="ml-3">Transactions Report</div>
                </a>
              </div>
              <div class="mb-2 font-medium">Users</div>
              <div class="mb-5">
                <a
                  v-for="(faker, fakerKey) in _.take(fakerData, 4)"
                  :key="fakerKey"
                  href=""
                  class="flex items-center mt-2"
                >
                  <div class="w-8 h-8 image-fit">
                    <img
                      alt="Midone Tailwind HTML Admin Template"
                      class="rounded-full"
                      :src="faker.photos[0]"
                    />
                  </div>
                  <div class="ml-3">{{ faker.users[0].name }}</div>
                  <div
                    class="w-48 ml-auto text-xs text-right truncate text-slate-500"
                  >
                    {{ faker.users[0].email }}
                  </div>
                </a>
              </div>
              <div class="mb-2 font-medium">Products</div>
              <a
                v-for="(faker, fakerKey) in _.take(fakerData, 4)"
                :key="fakerKey"
                href=""
                class="flex items-center mt-2"
              >
                <div class="w-8 h-8 image-fit">
                  <img
                    alt="Midone Tailwind HTML Admin Template"
                    class="rounded-full"
                    :src="faker.images[0]"
                  />
                </div>
                <div class="ml-3">{{ faker.products[0].name }}</div>
                <div
                  class="w-48 ml-auto text-xs text-right truncate text-slate-500"
                >
                  {{ faker.products[0].category }}
                </div>
              </a>
            </div>
          </div>
        </TransitionRoot>
      </div>
      <!-- END: Search -->
      <!-- BEGIN: Notifications -->
      <Popover class="mr-4 intro-x sm:mr-6">
        <Popover.Button
          class="relative text-white/70 outline-none block before:content-[''] before:w-[8px] before:h-[8px] before:rounded-full before:absolute before:top-[-2px] before:right-0 before:bg-danger"
        >
          <Lucide icon="Bell" class="w-5 h-5 dark:text-slate-500" />
        </Popover.Button>
        <Popover.Panel class="w-[280px] sm:w-[350px] p-5 mt-2">
          <div class="mb-5 font-medium">Notifications</div>
          <div
            v-for="(faker, fakerKey) in _.take(fakerData, 5)"
            :key="fakerKey"
            :class="[
              'cursor-pointer relative flex items-center',
              { 'mt-5': fakerKey },
            ]"
          >
            <div class="relative flex-none w-12 h-12 mr-1 image-fit">
              <img
                alt="Midone Tailwind HTML Admin Template"
                class="rounded-full"
                :src="faker.photos[0]"
              />
              <div
                class="absolute bottom-0 right-0 w-3 h-3 border-2 border-white rounded-full bg-success dark:border-darkmode-600"
              ></div>
            </div>
            <div class="ml-2 overflow-hidden">
              <div class="flex items-center">
                <a href="" class="mr-5 font-medium truncate">
                  {{ faker.users[0].name }}
                </a>
                <div class="ml-auto text-xs text-slate-400 whitespace-nowrap">
                  {{ faker.times[0] }}
                </div>
              </div>
              <div class="w-full truncate text-slate-500 mt-0.5">
                {{ faker.news[0].shortContent }}
              </div>
            </div>
          </div>
        </Popover.Panel>
      </Popover>
      <!-- END: Notifications -->
      <!-- BEGIN: Account Menu -->
      <Menu>
        <Menu.Button
          class="block w-8 h-8 overflow-hidden rounded-full shadow-lg image-fit zoom-in intro-x"
        >
          <img
            alt="Midone Tailwind HTML Admin Template"
            :src="fakerData[9].photos[0]"
          />
        </Menu.Button>
        <Menu.Items
          class="w-56 mt-px relative bg-primary/80 before:block before:absolute before:bg-black before:inset-0 before:rounded-md before:z-[-1] text-white"
        >
          <Menu.Header class="font-normal">
            <div class="font-medium">{{ fakerData[0].users[0].name }}</div>
            <div class="text-xs text-white/70 mt-0.5 dark:text-slate-500">
              {{ fakerData[0].jobs[0] }}
            </div>
          </Menu.Header>
          <Menu.Divider class="bg-white/[0.08]" />
          <Menu.Item class="hover:bg-white/5">
            <Lucide icon="User" class="w-4 h-4 mr-2" /> Profile
          </Menu.Item>
          <Menu.Item class="hover:bg-white/5">
            <Lucide icon="Edit" class="w-4 h-4 mr-2" /> Add Account
          </Menu.Item>
          <Menu.Item class="hover:bg-white/5">
            <Lucide icon="Lock" class="w-4 h-4 mr-2" /> Reset Password
          </Menu.Item>
          <Menu.Item class="hover:bg-white/5">
            <Lucide icon="HelpCircle" class="w-4 h-4 mr-2" /> Help
          </Menu.Item>
          <Menu.Divider class="bg-white/[0.08]" />
          <Menu.Item class="hover:bg-white/5">
            <Lucide icon="ToggleRight" class="w-4 h-4 mr-2" /> Logout
          </Menu.Item>
        </Menu.Items>
      </Menu>
      <!-- END: Account Menu -->
    </div>
  </div>
</template>
