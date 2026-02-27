<script setup lang="ts">
import { ref, computed } from "vue";
import Lucide from "@/components/Base/Lucide";
import { Menu, Slideover } from "@/components/Base/Headless";
import { useI18n } from "vue-i18n";
import { twMerge } from "tailwind-merge";

const { t } = useI18n();

interface SidebarPopProps {
  visible: boolean;
  theme?: "rubick" | "icewall" | "enigma" | "tinker";
  layout?: "side-menu" | "simple-menu" | "top-menu";
}

const props = withDefaults(defineProps<SidebarPopProps>(), {
  visible: true,
  theme: "rubick",
  layout: "side-menu",
});

const showSlideover = ref(false);
const toggleSlideover = (value: boolean) => {
  showSlideover.value = value;
};

const computedClass = computed(() =>
  twMerge([
    props.theme == "rubick" && props.layout == "side-menu" && "",
    props.theme == "enigma" && "text-white",
    props.theme == "tinker" && "text-white",
    props.theme == "icewall" && "text-white",
  ]),
);
</script>

<template>
  <Menu class="mr-4 intro-x sm:mr-6">
    <Menu.Button
      variant="primary"
      @click="
        (event: MouseEvent) => {
          event.preventDefault();
          toggleSlideover(true);
        }
      "
    >
      <Lucide icon="Archive" :class="computedClass" />
    </Menu.Button>
  </Menu>

  <Slideover
    :open="showSlideover"
    @close="
      () => {
        toggleSlideover(false);
      }
    "
  >
    <Slideover.Panel>
      <Slideover.Title class="p-5">
        <h2 class="mr-auto text-base font-medium">&nbsp;</h2>
      </Slideover.Title>
      <Slideover.Description>&nbsp;</Slideover.Description>
      <Slideover.Footer>
        <strong>
          {{ t("components.sidebar-pop.slide_over.footer.copyright") }} &copy;
          {{ new Date().getFullYear() }}
          <a href="https://www.github.com/GitzJoey">
            {{ t("components.sidebar-pop.slide_over.footer.copyright_name") }}
          </a>
          &nbsp;&amp;&nbsp;
          <a href="https://github.com/GitzJoey/DCSLab/graphs/contributors">
            {{ t("components.sidebar-pop.slide_over.footer.contributors") }}
          </a>
          .
        </strong>
        {{ t("components.sidebar-pop.slide_over.footer.rights") }}
        <br />
        {{ t("components.sidebar-pop.slide_over.footer.powered_by") }}
      </Slideover.Footer>
    </Slideover.Panel>
  </Slideover>
</template>
