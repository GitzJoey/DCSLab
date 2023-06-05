<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import { useRouter } from "vue-router";
import Lucide from "../../base-components/Lucide";
import Tippy from "../../base-components/Tippy";
import { FormattedMenu, linkTo } from "./simple-menu";

interface MenuProps {
  class?: string | object;
  menu: FormattedMenu;
  formattedMenuState: [
    (FormattedMenu | "divider")[],
    (computedFormattedMenu: Array<FormattedMenu | "divider">) => void
  ];
  level: "first" | "second" | "third";
}

const router = useRouter();
const props = defineProps<MenuProps>();
const [formattedMenu, setFormattedMenu] = props.formattedMenuState;
</script>

<template>
  <Tippy
    as="a"
    :options="{
      placement: 'left',
    }"
    :content="props.menu.title"
    :href="
      props.menu.subMenu
        ? '#'
        : ((pageName: string | undefined) => {
            try {
              return router.resolve({
                name: pageName,
              }).fullPath;
            } catch (err) {
              return '';
            }
          })(props.menu.pageName)
    "
    :class="[
      'h-[50px] flex items-center pl-5 text-slate-600 mb-1 relative rounded-xl dark:text-slate-300',
      {
        'text-slate-600 dark:text-slate-400':
          !props.menu.active && props.level != 'first',
        'bg-slate-100 dark:bg-transparent':
          props.menu.active && props.level == 'first',
        'before:content-[\'\'] before:block before:inset-0 before:rounded-xl before:absolute before:border-b-[3px] before:border-solid before:border-black/[0.08] before:dark:border-black/[0.08] before:dark:bg-darkmode-700':
          props.menu.active && props.level == 'first',
        'after:content-[\'\'] after:w-[20px] after:h-[80px] after:mr-[-27px] after:bg-menu-active after:bg-no-repeat after:bg-cover after:absolute after:top-0 after:bottom-0 after:right-0 after:my-auto after:dark:bg-menu-active-dark':
          props.menu.active && props.level == 'first',
        'hover:bg-slate-100 hover:dark:bg-transparent hover:before:content-[\'\'] hover:before:block hover:before:inset-0 hover:before:rounded-xl hover:before:absolute hover:before:z-[-1] hover:before:border-b-[3px] hover:before:border-solid hover:before:border-black/[0.08] hover:before:dark:bg-darkmode-700':
          !props.menu.active &&
          !props.menu.activeDropdown &&
          props.level == 'first',

        // Animation
        'after:-mr-[47px] after:opacity-0 after:animate-[0.4s_ease-in-out_0.1s_active-side-menu-chevron] after:animate-fill-mode-forwards':
          props.menu.active && props.level == 'first',
      },
      props.class,
    ]"
    @click="(event: MouseEvent) => {
        event.preventDefault();
        linkTo(props.menu, router);
        setFormattedMenu([...formattedMenu]);
    }"
  >
    <div
      :class="{
        'text-primary z-10 dark:text-slate-300':
          props.menu.active && props.level == 'first',
        'text-slate-700 dark:text-slate-300':
          props.menu.active && props.level != 'first',
        'dark:text-slate-400': !props.menu.active,
      }"
    >
      <Lucide :icon="props.menu.icon" />
    </div>
  </Tippy>
</template>
