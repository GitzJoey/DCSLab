<script setup lang="ts">
import { HTMLAttributes, ref, onMounted } from "vue";
import { Draggable as FullCalendarDraggable } from "@fullcalendar/interaction";

interface DraggableProps extends /* @vue-ignore */ HTMLAttributes {
  options: FullCalendarDraggable["settings"];
}

const props = defineProps<DraggableProps>();

const draggableRef = ref<HTMLDivElement>();

const init = () => {
  if (draggableRef.value) {
    new FullCalendarDraggable(draggableRef.value, props.options);
  }
};

onMounted(() => {
  init();
});
</script>

<template>
  <div ref="draggableRef">
    <slot></slot>
  </div>
</template>
