<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import { useRouter } from "vue-router";
import Lucide from "../../base-components/Lucide";
import { FormattedMenu } from "../../layouts/SideMenu/side-menu";
import { linkTo } from "./mobile-menu";
import { useI18n } from "vue-i18n";

interface MenuProps {
  menu: FormattedMenu;
  formattedMenuState: [
    (FormattedMenu | "divider")[],
    (computedFormattedMenu: Array<FormattedMenu | "divider">) => void
  ];
  level: "first" | "second" | "third";
  setActiveMobileMenu: (active: boolean) => void;
}

const { t } = useI18n();
const router = useRouter();
const props = defineProps<MenuProps>();
const [formattedMenu, setFormattedMenu] = props.formattedMenuState;
</script>

<template>
  <a :href="props.menu.subMenu
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
    " :class="[
    'h-[50px] flex items-center text-white',
    props.level == 'first' && 'px-6',
    props.level != 'first' && 'px-4',
  ]" @click="(event) => {
  event.preventDefault();
  linkTo(props.menu, router, props.setActiveMobileMenu);
  setFormattedMenu([...formattedMenu]);
}
  ">
    <div>
      <Lucide :icon="props.menu.icon" />
    </div>
    <div class="flex items-center w-full ml-3">
      {{ t(props.menu.title) }}
      <div v-if="props.menu.subMenu" :class="[
        'transition ease-in duration-100 ml-auto',
        props.menu.activeDropdown && 'transform rotate-180',
      ]">
        <Lucide icon="ChevronDown" class="w-5 h-5" />
      </div>
    </div>
  </a>
</template>
