<script lang="ts" setup>
import {
  PaginationContext,
  PaginationRoot,
  PaginationItem,
  PaginationPrevTrigger,
  PaginationNextTrigger,
  PaginationEllipsis,
} from "@/components/ui/pagination";
</script>

<template>
  <PaginationRoot :count="5000" :pageSize="10" :siblingCount="2">
    <PaginationPrevTrigger>Previous</PaginationPrevTrigger>
    <PaginationContext v-slot="{ pagination }">
      <template v-for="(page, index) in pagination?.pages" :key="index">
        <PaginationItem v-if="page.type === 'page'" v-bind="{ ...page }">
          {{ page.value }}
        </PaginationItem>
        <PaginationEllipsis v-else :index="index" />
      </template>
    </PaginationContext>
    <PaginationNextTrigger>Next</PaginationNextTrigger>
  </PaginationRoot>
</template>
