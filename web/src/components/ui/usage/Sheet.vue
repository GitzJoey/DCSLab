<script lang="ts" setup>
import {
  SheetRoot,
  SheetTrigger,
  SheetContent,
  SheetTitle,
  SheetDescription,
  SheetCloseTrigger,
} from "@/components/ui/sheet";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { SquareX, Save, ExternalLink } from "lucide-vue-next";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <div class="text-sm text-muted-foreground">
          <SheetRoot>
            <SheetTrigger>Open Sheet</SheetTrigger>
            <SheetContent>
              <SheetTitle>Sheet Title</SheetTitle>
              <SheetDescription>
                Make changes to your profile here. Click save when you're done.
              </SheetDescription>
              <div class="grid gap-4 mt-2">
                <div class="grid gap-2.5">
                  <Label htmlFor="name-1">Name</Label>
                  <Input id="name-1" name="name" defaultValue="Pedro Duarte" />
                </div>
                <div class="grid gap-2.5">
                  <Label htmlFor="username-1">Username</Label>
                  <Input
                    id="username-1"
                    name="username"
                    defaultValue="@peduarte"
                  />
                </div>
              </div>
              <div class="flex gap-2 justify-end mt-7">
                <SheetCloseTrigger>
                  <SquareX />
                  Close
                </SheetCloseTrigger>
                <Button variant="primary">
                  <Save />
                  Submit
                </Button>
              </div>
              <SheetCloseTrigger />
            </SheetContent>
          </SheetRoot>
        </div>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <div class="text-sm text-muted-foreground">
          <SheetRoot>
            <SheetTrigger>Custom Close</SheetTrigger>
            <SheetContent>
              <SheetTitle>Share Link</SheetTitle>
              <SheetDescription>
                Anyone who has this link will be able to view this.
              </SheetDescription>
              <div class="grid gap-4 mt-2">
                <Input
                  id="name-1"
                  name="name"
                  defaultValue="https://midone-ui.com/docs/installation"
                />
              </div>
              <div class="flex gap-2 mt-5">
                <SheetCloseTrigger>
                  <ExternalLink />
                  Share Link
                </SheetCloseTrigger>
              </div>
            </SheetContent>
          </SheetRoot>
        </div>
      </div>
    </div>
  </div>
</template>
