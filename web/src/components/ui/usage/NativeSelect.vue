<script setup lang="ts">
import {
  NativeSelect,
  NativeSelectOption,
  NativeSelectOptionGroup,
} from "@/components/ui/native-select";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid sm:grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <NativeSelect class="w-56">
          <NativeSelectOption value="">Select status</NativeSelectOption>
          <NativeSelectOption value="todo">Todo</NativeSelectOption>
          <NativeSelectOption value="in-progress"
            >In Progress</NativeSelectOption
          >
          <NativeSelectOption value="done">Done</NativeSelectOption>
          <NativeSelectOption value="cancelled">Cancelled</NativeSelectOption>
        </NativeSelect>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <NativeSelect class="w-56">
          <NativeSelectOption value="">Select department</NativeSelectOption>
          <NativeSelectOptionGroup label="Engineering">
            <NativeSelectOption value="frontend">Frontend</NativeSelectOption>
            <NativeSelectOption value="backend">Backend</NativeSelectOption>
            <NativeSelectOption value="devops">DevOps</NativeSelectOption>
          </NativeSelectOptionGroup>
          <NativeSelectOptionGroup label="Sales">
            <NativeSelectOption value="sales-rep">Sales Rep</NativeSelectOption>
            <NativeSelectOption value="account-manager">
              Account Manager
            </NativeSelectOption>
            <NativeSelectOption value="sales-director">
              Sales Director
            </NativeSelectOption>
          </NativeSelectOptionGroup>
          <NativeSelectOptionGroup label="Operations">
            <NativeSelectOption value="support">
              Customer Support
            </NativeSelectOption>
            <NativeSelectOption value="product-manager">
              Product Manager
            </NativeSelectOption>
            <NativeSelectOption value="ops-manager">
              Operations Manager
            </NativeSelectOption>
          </NativeSelectOptionGroup>
        </NativeSelect>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <NativeSelect class="w-56" disabled>
          <NativeSelectOption value="">Select status</NativeSelectOption>
          <NativeSelectOption value="todo">Todo</NativeSelectOption>
          <NativeSelectOption value="in-progress"
            >In Progress</NativeSelectOption
          >
          <NativeSelectOption value="done">Done</NativeSelectOption>
          <NativeSelectOption value="cancelled">Cancelled</NativeSelectOption>
        </NativeSelect>
      </div>
    </div>
  </div>
</template>
