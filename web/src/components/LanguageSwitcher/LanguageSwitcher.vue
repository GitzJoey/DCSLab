<script setup lang="ts">
import { Menu } from "@/components/Base/Headless";
import { switchLang } from "@/lang";
import { useI18n } from "vue-i18n";
import Lucide from "@/components/Base/Lucide";
import { twMerge } from "tailwind-merge";
import { computed } from "vue";

const { t } = useI18n();

interface LanguageSwitcherProps {
  visible: boolean;
  theme?: "rubick" | "icewall" | "enigma" | "tinker";
  layout?: "side-menu" | "simple-menu" | "top-menu";
}

const props = withDefaults(defineProps<LanguageSwitcherProps>(), {
  visible: true,
  theme: "rubick",
  layout: "side-menu",
});

const switchLanguage = (lang: "en" | "id"): void => {
  switchLang(lang);
};

const computedClass = computed(() =>
  twMerge([
    props.theme == "rubick" && props.layout == "side-menu" && "",
    props.theme == "enigma" && "text-white",
    props.theme == "tinker" && "text-white",
    props.theme == "icewall" && "text-white",
    "hover:animate-spin",
  ]),
);
</script>

<template>
  <Menu class="mr-4 intro-x sm:mr-6">
    <Menu.Button variant="primary">
      <Lucide icon="Globe" :class="computedClass" />
    </Menu.Button>
    <Menu.Items class="w-48 h-24 overflow-y-auto" placement="bottom-end">
      <Menu.Item @click="switchLanguage('en')">
        <span class="text-primary">{{ t("components.language-switcher.language.english") }}</span>
      </Menu.Item>
      <Menu.Item @click="switchLanguage('id')">
        <span class="text-primary">{{ t("components.language-switcher.language.indonesia") }}</span>
      </Menu.Item>
    </Menu.Items>
  </Menu>
</template>
