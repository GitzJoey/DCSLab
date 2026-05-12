<script setup lang="ts">
import { computed, ref, watch } from 'vue';

export interface TreeListNode {
  id: string | number;
  children?: Array<TreeListNode>;
  [key: string]: any;
}

interface TreeListVisibleRow {
  item: TreeListNode;
  depth: number;
  hasChildren: boolean;
  isExpanded: boolean;
}

interface TreeListProps {
  items: Array<TreeListNode>;
  itemKey?: string;
  childrenKey?: string;
}

const props = withDefaults(defineProps<TreeListProps>(), {
  itemKey: 'id',
  childrenKey: 'children',
});

const expandedIds = ref<Array<string | number>>([]);

const expandedIdSet = computed(() => new Set(expandedIds.value));

const getItemKey = (item: TreeListNode): string | number => {
  return (item as Record<string, string | number>)[props.itemKey] ?? item.id;
};

const getChildren = (item: TreeListNode): Array<TreeListNode> => {
  const children = (item as Record<string, unknown>)[props.childrenKey];

  return Array.isArray(children) ? (children as Array<TreeListNode>) : [];
};

const collectExpandableIds = (items: Array<TreeListNode>): Array<string | number> => {
  const result: Array<string | number> = [];

  const walk = (nodes: Array<TreeListNode>) => {
    for (const node of nodes) {
      const children = getChildren(node);

      if (children.length > 0) {
        result.push(getItemKey(node));
        walk(children);
      }
    }
  };

  walk(items);

  return result;
};

const toggleExpanded = (item: TreeListNode) => {
  const itemId = getItemKey(item);

  if (expandedIdSet.value.has(itemId)) {
    expandedIds.value = expandedIds.value.filter((id) => id !== itemId);
    return;
  }

  expandedIds.value = [...expandedIds.value, itemId];
};

const expandAll = () => {
  expandedIds.value = collectExpandableIds(props.items);
};

const collapseAll = () => {
  expandedIds.value = [];
};

const visibleRows = computed<Array<TreeListVisibleRow>>(() => {
  const result: Array<TreeListVisibleRow> = [];

  const walk = (items: Array<TreeListNode>, depth: number) => {
    for (const item of items) {
      const children = getChildren(item);
      const itemId = getItemKey(item);
      const hasChildren = children.length > 0;
      const isExpanded = expandedIdSet.value.has(itemId);

      result.push({
        item,
        depth,
        hasChildren,
        isExpanded,
      });

      if (hasChildren && isExpanded) {
        walk(children, depth + 1);
      }
    }
  };

  walk(props.items, 0);

  return result;
});

watch(
  () => props.items,
  () => {
    expandedIds.value = [];
  },
);

defineExpose({
  expandAll,
  collapseAll,
});
</script>

<template>
  <TransitionGroup name="tree-row">
    <template v-for="row in visibleRows" :key="String(getItemKey(row.item))">
      <slot
        name="row"
        :item="row.item"
        :depth="row.depth"
        :has-children="row.hasChildren"
        :is-expanded="row.isExpanded"
        :toggle="() => toggleExpanded(row.item)"
      />
    </template>
  </TransitionGroup>
</template>

<style>
.tree-row-enter-active,
.tree-row-leave-active {
  transition: opacity 180ms ease, transform 180ms ease;
}

.tree-row-enter-from,
.tree-row-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

.tree-row-move {
  transition: transform 180ms ease;
}
</style>
