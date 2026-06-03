<script lang="ts" setup>
import { Button } from "@/components/ui/button";
import {
  toaster,
  ToasterContainer,
  ToastRoot,
  ToastTitle,
  ToastDescription,
  ToastCloseTrigger,
} from "@/components/ui/toast";
</script>

<template>
  <Button
    look="outline"
    variant="secondary"
    @click="
      () =>
        toaster.create({
          title: 'Event has been created',
          description: 'Sunday, December 03, 2023 at 9:00 AM',
          type: 'info',
        })
    "
  >
    Show Toast
  </Button>
  <ToasterContainer :toaster="toaster" v-slot="{ toast }">
    <ToastRoot :key="toast.id">
      <ToastTitle>{{ toast.title }}</ToastTitle>
      <ToastDescription>
        {{ toast.description }}
      </ToastDescription>
      <ToastCloseTrigger />
    </ToastRoot>
  </ToasterContainer>
</template>
