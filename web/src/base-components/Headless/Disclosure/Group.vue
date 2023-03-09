<script lang="ts">
type Variant = "default" | "boxed";
</script>

<script setup lang="ts">
import { ref, provide, useSlots, ComputedRef, computed } from "vue";

export type ProvideGroup = ComputedRef<{
  selectedIndex?: number;
  setSelectedIndex: (value: number) => void;
  variant?: Variant;
}>;

interface GroupProps {
  as?: string | object;
  selectedIndex?: number;
  variant?: Variant;
}

const slots = useSlots();

const { as, selectedIndex, variant } = withDefaults(defineProps<GroupProps>(), {
  as: "div",
  selectedIndex: 0,
  variant: "default",
});

const active = ref(selectedIndex);
const setActive = (value: number) => {
  active.value = value;
};

provide<ProvideGroup>(
  "group",
  computed(() => {
    return {
      selectedIndex: active.value,
      setSelectedIndex: setActive,
      variant: variant,
    };
  })
);
</script>

<template>
  <component :is="as">
    <component
      v-for="(item, key) in slots.default && slots.default()"
      :is="item"
      :index="key"
    />
  </component>
</template>
