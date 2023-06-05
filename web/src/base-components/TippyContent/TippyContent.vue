<script setup lang="ts">
import tippy, {
  PopperElement,
  Props,
  roundArrow,
  animateFill as animateFillPlugin,
} from "tippy.js";
import { ref, onMounted, inject } from "vue";

export type ProvideTippy = (el: PopperElement) => void;

interface TippyContentProps {
  to: string;
  refKey?: string;
  options?: Partial<Props>;
}

const props = defineProps<TippyContentProps>();

const tippyRef = ref<PopperElement>();

const init = (el: PopperElement, props: TippyContentProps) => {
  tippy(`[data-tooltip="${props.to}"]`, {
    plugins: [animateFillPlugin],
    content: el,
    allowHTML: true,
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
    theme: "light",
    trigger: "click",
    ...props.options,
  });
};

const bindInstance = (el: PopperElement) => {
  if (props.refKey) {
    const bind = inject<ProvideTippy>(`bind[${props.refKey}]`);
    if (bind) {
      bind(el);
    }
  }
};

onMounted(() => {
  if (tippyRef.value) {
    init(tippyRef.value, props);
    bindInstance(tippyRef.value);
  }
});
</script>

<template>
  <div ref="tippyRef">
    <slot></slot>
  </div>
</template>
