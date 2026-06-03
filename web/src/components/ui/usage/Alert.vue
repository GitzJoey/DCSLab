<script lang="ts" setup>
import {
  AlertRoot,
  AlertTitle,
  AlertDescription,
  AlertCloseTrigger,
} from "@/components/ui/alert";
import { Compass } from "lucide-vue-next";
import { Box } from "@/components/ui/box";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <AlertRoot variant="primary">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertDescription>
            This is an alert with icon, title and description.
          </AlertDescription>
          <AlertCloseTrigger />
        </AlertRoot>
        <AlertRoot variant="secondary">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertDescription>
            This is an alert with icon, title and description.
          </AlertDescription>
          <AlertCloseTrigger />
        </AlertRoot>
        <AlertRoot variant="success">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertDescription>
            This is an alert with icon, title and description.
          </AlertDescription>
          <AlertCloseTrigger />
        </AlertRoot>
        <AlertRoot variant="danger">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertDescription>
            This is an alert with icon, title and description.
          </AlertDescription>
          <AlertCloseTrigger />
        </AlertRoot>
        <AlertRoot variant="pending">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertDescription>
            This is an alert with icon, title and description.
          </AlertDescription>
          <AlertCloseTrigger />
        </AlertRoot>
        <AlertRoot variant="warning">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertDescription>
            This is an alert with icon, title and description.
          </AlertDescription>
          <AlertCloseTrigger />
        </AlertRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <AlertRoot look="filled" variant="primary">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertDescription>
            This is an alert with icon, title and description.
          </AlertDescription>
          <AlertCloseTrigger />
        </AlertRoot>
        <AlertRoot look="filled" variant="secondary">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertDescription>
            This is an alert with icon, title and description.
          </AlertDescription>
          <AlertCloseTrigger />
        </AlertRoot>
        <AlertRoot look="filled" variant="success">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertDescription>
            This is an alert with icon, title and description.
          </AlertDescription>
          <AlertCloseTrigger />
        </AlertRoot>
        <AlertRoot look="filled" variant="danger">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertDescription>
            This is an alert with icon, title and description.
          </AlertDescription>
          <AlertCloseTrigger />
        </AlertRoot>
        <AlertRoot look="filled" variant="pending">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertDescription>
            This is an alert with icon, title and description.
          </AlertDescription>
          <AlertCloseTrigger />
        </AlertRoot>
        <AlertRoot look="filled" variant="warning">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertDescription>
            This is an alert with icon, title and description.
          </AlertDescription>
          <AlertCloseTrigger />
        </AlertRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <AlertRoot raised="single" variant="primary">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertCloseTrigger />
        </AlertRoot>
        <AlertRoot raised="single" variant="secondary">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertCloseTrigger />
        </AlertRoot>
        <AlertRoot raised="single" variant="success">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertCloseTrigger />
        </AlertRoot>
        <AlertRoot raised="single" variant="danger">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertCloseTrigger />
        </AlertRoot>
        <AlertRoot raised="single" variant="pending">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertCloseTrigger />
        </AlertRoot>
        <AlertRoot raised="single" variant="warning">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
          <AlertCloseTrigger />
        </AlertRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <AlertRoot look="filled" raised="double" variant="primary">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
        </AlertRoot>
        <AlertRoot look="filled" raised="double" variant="secondary">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
        </AlertRoot>
        <AlertRoot look="filled" raised="double" variant="success">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
        </AlertRoot>
        <AlertRoot look="filled" raised="double" variant="danger">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
        </AlertRoot>
        <AlertRoot look="filled" raised="double" variant="pending">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
        </AlertRoot>
        <AlertRoot look="filled" raised="double" variant="warning">
          <Compass />
          <AlertTitle>Success! Your changes have been saved</AlertTitle>
        </AlertRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Box class="p-0">
          <AlertRoot variant="ghost">
            <Compass />
            <AlertTitle>Success! Your changes have been saved</AlertTitle>
            <AlertDescription>
              This is an alert with icon, title and description.
            </AlertDescription>
            <AlertCloseTrigger />
          </AlertRoot>
        </Box>
        <Box class="p-0" raised="single">
          <AlertRoot variant="ghost">
            <Compass />
            <AlertTitle>Success! Your changes have been saved</AlertTitle>
            <AlertDescription>
              This is an alert with icon, title and description.
            </AlertDescription>
            <AlertCloseTrigger />
          </AlertRoot>
        </Box>
        <Box class="p-0" raised="double">
          <AlertRoot variant="ghost">
            <Compass />
            <AlertTitle>Success! Your changes have been saved</AlertTitle>
            <AlertDescription>
              This is an alert with icon, title and description.
            </AlertDescription>
            <AlertCloseTrigger />
          </AlertRoot>
        </Box>
      </div>
    </div>
  </div>
</template>
