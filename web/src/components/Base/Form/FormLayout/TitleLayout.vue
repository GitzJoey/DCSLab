<script setup lang="ts"></script>

<template>
  <div class="flex flex-col items-center intro-y sm:flex-row">
    <h2 class="mr-auto text-lg font-medium">
      <slot name="title"></slot>
    </h2>
    <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
      <slot name="optional"></slot>
    </div>
  </div>
</template>
