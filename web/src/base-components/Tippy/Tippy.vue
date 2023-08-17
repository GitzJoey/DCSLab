<script setup lang="ts">
import tippy, {
  PopperElement,
  Props,
  roundArrow,
  animateFill as animateFillPlugin,
} from "tippy.js";
import { ref, onMounted, inject, watch } from "vue";

export type ProvideTippy = (el: PopperElement) => void;

interface TippyProps {
  refKey?: string;
  content: string;
  disable?: boolean;
  as?: string | object;
  options?: Partial<Props>;
}

const props = withDefaults(defineProps<TippyProps>(), {
  as: "span",
  disable: false,
});

const tippyRef = ref<PopperElement>();

const init = (el: PopperElement, props: TippyProps) => {
  tippy(el, {
    plugins: [animateFillPlugin],
    content: props.content,
    arrow: roundArrow,
    popperOptions: {
      modifiers: [
        {
          name: "preventOverflow",
          options: {
            rootBoundary: "viewport",
          },
        },
      ],
    },
    animateFill: false,
    animation: "shift-away",
    ...props.options,
  });
};

const bindInstance = (el: PopperElement) => {
  if (props.refKey) {
    const bind = inject<ProvideTippy>(`bind[${props.refKey}]`, () => {});
    if (bind) {
      bind(el);
    }
  }
};

const vTippyDirective = {
  mounted(el: PopperElement) {
    tippyRef.value = el;
  },
};

const isDisabled = () => {
  if (tippyRef.value && tippyRef.value._tippy !== undefined) {
    props.disable
      ? tippyRef.value._tippy.disable()
      : tippyRef.value._tippy.enable();
  }
};

watch(props, () => {
  isDisabled();
});

onMounted(() => {
  if (tippyRef.value) {
    init(tippyRef.value, props);
    bindInstance(tippyRef.value);
    isDisabled();
  }
});
</script>

<template>
  <component :is="as" v-tippy-directive class="cursor-pointer">
    <slot></slot>
  </component>
</template>
