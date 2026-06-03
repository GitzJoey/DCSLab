<script lang="ts" setup>
import { Button } from "@/components/ui/button";
import {
  LoaderCircle,
  Copy,
  Scissors,
  Trash,
  SquarePlus,
} from "lucide-vue-next";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="primary" size="sm"> Button Small </Button>
        <Button variant="primary">Button Medium</Button>
        <Button variant="primary" size="lg"> Button Large </Button>
        <Button variant="primary" size="xl"> Button Extra Large </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="secondary" size="sm"> Button Small </Button>
        <Button variant="secondary">Button Medium</Button>
        <Button variant="secondary" size="lg"> Button Large </Button>
        <Button variant="secondary" size="xl"> Button Extra Large </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="success" size="sm"> Button Small </Button>
        <Button variant="success">Button Medium</Button>
        <Button variant="success" size="lg"> Button Large </Button>
        <Button variant="success" size="xl"> Button Extra Large </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="danger" size="sm"> Button Small </Button>
        <Button variant="danger">Button Medium</Button>
        <Button variant="danger" size="lg"> Button Large </Button>
        <Button variant="danger" size="xl"> Button Extra Large </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="pending" size="sm"> Button Small </Button>
        <Button variant="pending">Button Medium</Button>
        <Button variant="pending" size="lg"> Button Large </Button>
        <Button variant="pending" size="xl"> Button Extra Large </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="warning" size="sm"> Button Small </Button>
        <Button variant="warning">Button Medium</Button>
        <Button variant="warning" size="lg"> Button Large </Button>
        <Button variant="warning" size="xl"> Button Extra Large </Button>
      </div>
    </div>
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="outline" variant="primary" size="sm">
          Button Small
        </Button>
        <Button look="outline" variant="primary">Button Medium</Button>
        <Button look="outline" variant="primary" size="lg">
          Button Large
        </Button>
        <Button look="outline" variant="primary" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="outline" variant="secondary" size="sm">
          Button Small
        </Button>
        <Button look="outline" variant="secondary">Button Medium</Button>
        <Button look="outline" variant="secondary" size="lg">
          Button Large
        </Button>
        <Button look="outline" variant="secondary" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="outline" variant="success" size="sm">
          Button Small
        </Button>
        <Button look="outline" variant="success">Button Medium</Button>
        <Button look="outline" variant="success" size="lg">
          Button Large
        </Button>
        <Button look="outline" variant="success" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="outline" variant="danger" size="sm">
          Button Small
        </Button>
        <Button look="outline" variant="danger">Button Medium</Button>
        <Button look="outline" variant="danger" size="lg">
          Button Large
        </Button>
        <Button look="outline" variant="danger" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="outline" variant="pending" size="sm">
          Button Small
        </Button>
        <Button look="outline" variant="pending">Button Medium</Button>
        <Button look="outline" variant="pending" size="lg">
          Button Large
        </Button>
        <Button look="outline" variant="pending" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="outline" variant="warning" size="sm">
          Button Small
        </Button>
        <Button look="outline" variant="warning">Button Medium</Button>
        <Button look="outline" variant="warning" size="lg">
          Button Large
        </Button>
        <Button look="outline" variant="warning" size="xl">
          Button Extra Large
        </Button>
      </div>
    </div>
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="primary" size="sm">
          Button Small
        </Button>
        <Button look="filled" variant="primary"> Button Medium </Button>
        <Button look="filled" variant="primary" size="lg">
          Button Large
        </Button>
        <Button look="filled" variant="primary" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="secondary" size="sm">
          Button Small
        </Button>
        <Button look="filled" variant="secondary"> Button Medium </Button>
        <Button look="filled" variant="secondary" size="lg">
          Button Large
        </Button>
        <Button look="filled" variant="secondary" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="success" size="sm">
          Button Small
        </Button>
        <Button look="filled" variant="success"> Button Medium </Button>
        <Button look="filled" variant="success" size="lg">
          Button Large
        </Button>
        <Button look="filled" variant="success" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="danger" size="sm"> Button Small </Button>
        <Button look="filled" variant="danger"> Button Medium </Button>
        <Button look="filled" variant="danger" size="lg"> Button Large </Button>
        <Button look="filled" variant="danger" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="pending" size="sm">
          Button Small
        </Button>
        <Button look="filled" variant="pending"> Button Medium </Button>
        <Button look="filled" variant="pending" size="lg">
          Button Large
        </Button>
        <Button look="filled" variant="pending" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="warning" size="sm">
          Button Small
        </Button>
        <Button look="filled" variant="warning"> Button Medium </Button>
        <Button look="filled" variant="warning" size="lg">
          Button Large
        </Button>
        <Button look="filled" variant="warning" size="xl">
          Button Extra Large
        </Button>
      </div>
    </div>
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled variant="primary" size="sm"> Button Small </Button>
        <Button disabled variant="primary"> Button Medium </Button>
        <Button disabled variant="primary" size="lg"> Button Large </Button>
        <Button disabled variant="primary" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled variant="secondary" size="sm"> Button Small </Button>
        <Button disabled variant="secondary"> Button Medium </Button>
        <Button disabled variant="secondary" size="lg"> Button Large </Button>
        <Button disabled variant="secondary" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled variant="success" size="sm"> Button Small </Button>
        <Button disabled variant="success"> Button Medium </Button>
        <Button disabled variant="success" size="lg"> Button Large </Button>
        <Button disabled variant="success" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled variant="danger" size="sm"> Button Small </Button>
        <Button disabled variant="danger"> Button Medium </Button>
        <Button disabled variant="danger" size="lg"> Button Large </Button>
        <Button disabled variant="danger" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled variant="pending" size="sm"> Button Small </Button>
        <Button disabled variant="pending"> Button Medium </Button>
        <Button disabled variant="pending" size="lg"> Button Large </Button>
        <Button disabled variant="pending" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled variant="warning" size="sm"> Button Small </Button>
        <Button disabled variant="warning"> Button Medium </Button>
        <Button disabled variant="warning" size="lg"> Button Large </Button>
        <Button disabled variant="warning" size="xl">
          Button Extra Large
        </Button>
      </div>
    </div>
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled look="outline" variant="primary" size="sm">
          Button Small
        </Button>
        <Button disabled look="outline" variant="primary">Button Medium</Button>
        <Button disabled look="outline" variant="primary" size="lg">
          Button Large
        </Button>
        <Button disabled look="outline" variant="primary" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled look="outline" variant="secondary" size="sm">
          Button Small
        </Button>
        <Button disabled look="outline" variant="secondary"
          >Button Medium</Button
        >
        <Button disabled look="outline" variant="secondary" size="lg">
          Button Large
        </Button>
        <Button disabled look="outline" variant="secondary" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled look="outline" variant="success" size="sm">
          Button Small
        </Button>
        <Button disabled look="outline" variant="success">Button Medium</Button>
        <Button disabled look="outline" variant="success" size="lg">
          Button Large
        </Button>
        <Button disabled look="outline" variant="success" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled look="outline" variant="danger" size="sm">
          Button Small
        </Button>
        <Button disabled look="outline" variant="danger">Button Medium</Button>
        <Button disabled look="outline" variant="danger" size="lg">
          Button Large
        </Button>
        <Button disabled look="outline" variant="danger" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled look="outline" variant="pending" size="sm">
          Button Small
        </Button>
        <Button disabled look="outline" variant="pending">Button Medium</Button>
        <Button disabled look="outline" variant="pending" size="lg">
          Button Large
        </Button>
        <Button disabled look="outline" variant="pending" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled look="outline" variant="warning" size="sm">
          Button Small
        </Button>
        <Button disabled look="outline" variant="warning">Button Medium</Button>
        <Button disabled look="outline" variant="warning" size="lg">
          Button Large
        </Button>
        <Button disabled look="outline" variant="warning" size="xl">
          Button Extra Large
        </Button>
      </div>
    </div>
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled look="filled" variant="primary" size="sm">
          Button Small
        </Button>
        <Button disabled look="filled" variant="primary">
          Button Medium
        </Button>
        <Button disabled look="filled" variant="primary" size="lg">
          Button Large
        </Button>
        <Button disabled look="filled" variant="primary" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled look="filled" variant="secondary" size="sm">
          Button Small
        </Button>
        <Button disabled look="filled" variant="secondary">
          Button Medium
        </Button>
        <Button disabled look="filled" variant="secondary" size="lg">
          Button Large
        </Button>
        <Button disabled look="filled" variant="secondary" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled look="filled" variant="success" size="sm">
          Button Small
        </Button>
        <Button disabled look="filled" variant="success">
          Button Medium
        </Button>
        <Button disabled look="filled" variant="success" size="lg">
          Button Large
        </Button>
        <Button disabled look="filled" variant="success" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled look="filled" variant="danger" size="sm">
          Button Small
        </Button>
        <Button disabled look="filled" variant="danger"> Button Medium </Button>
        <Button disabled look="filled" variant="danger" size="lg">
          Button Large
        </Button>
        <Button disabled look="filled" variant="danger" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled look="filled" variant="pending" size="sm">
          Button Small
        </Button>
        <Button disabled look="filled" variant="pending">
          Button Medium
        </Button>
        <Button disabled look="filled" variant="pending" size="lg">
          Button Large
        </Button>
        <Button disabled look="filled" variant="pending" size="xl">
          Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button disabled look="filled" variant="warning" size="sm">
          Button Small
        </Button>
        <Button disabled look="filled" variant="warning">
          Button Medium
        </Button>
        <Button disabled look="filled" variant="warning" size="lg">
          Button Large
        </Button>
        <Button disabled look="filled" variant="warning" size="xl">
          Button Extra Large
        </Button>
      </div>
    </div>
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button>
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="primary" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button variant="primary">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button variant="primary" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button variant="primary" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="secondary" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button variant="secondary">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button variant="secondary" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button variant="secondary" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="success" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button variant="success">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button variant="success" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button variant="success" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="danger" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button variant="danger">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button variant="danger" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button variant="danger" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="pending" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button variant="pending">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button variant="pending" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button variant="pending" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="warning" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button variant="warning">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button variant="warning" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button variant="warning" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
    </div>
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="outline" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button look="outline">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button look="outline" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button look="outline" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="outline" variant="primary" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button look="outline" variant="primary">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button look="outline" variant="primary" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button look="outline" variant="primary" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="outline" variant="secondary" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button look="outline" variant="secondary">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button look="outline" variant="secondary" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button look="outline" variant="secondary" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="outline" variant="success" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button look="outline" variant="success">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button look="outline" variant="success" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button look="outline" variant="success" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="outline" variant="danger" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button look="outline" variant="danger">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button look="outline" variant="danger" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button look="outline" variant="danger" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="outline" variant="pending" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button look="outline" variant="pending">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button look="outline" variant="pending" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button look="outline" variant="pending" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="outline" variant="warning" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button look="outline" variant="warning">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button look="outline" variant="warning" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button look="outline" variant="warning" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
    </div>
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button look="filled">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button look="filled" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button look="filled" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="primary" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button look="filled" variant="primary">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button look="filled" variant="primary" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button look="filled" variant="primary" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="secondary" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button look="filled" variant="secondary">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button look="filled" variant="secondary" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button look="filled" variant="secondary" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="success" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button look="filled" variant="success">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button look="filled" variant="success" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button look="filled" variant="success" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="danger" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button look="filled" variant="danger">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button look="filled" variant="danger" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button look="filled" variant="danger" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="pending" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button look="filled" variant="pending">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button look="filled" variant="pending" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button look="filled" variant="pending" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="warning" size="sm">
          <LoaderCircle class="size-4 animate-spin" /> Button Small
        </Button>
        <Button look="filled" variant="warning">
          <LoaderCircle class="size-4 animate-spin" /> Button Medium
        </Button>
        <Button look="filled" variant="warning" size="lg">
          <LoaderCircle class="size-4 animate-spin" /> Button Large
        </Button>
        <Button look="filled" variant="warning" size="xl">
          <LoaderCircle class="size-4 animate-spin" /> Button Extra Large
        </Button>
      </div>
    </div>
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button size="sm">
          <Copy class="size-4" />
        </Button>
        <Button>
          <Scissors class="size-4" />
        </Button>
        <Button size="lg">
          <Trash class="size-4" />
        </Button>
        <Button size="xl">
          <SquarePlus class="size-4" />
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="primary" size="sm">
          <Copy class="size-4" />
        </Button>
        <Button variant="primary">
          <Scissors class="size-4" />
        </Button>
        <Button variant="primary" size="lg">
          <Trash class="size-4" />
        </Button>
        <Button variant="primary" size="xl">
          <SquarePlus class="size-4" />
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="secondary" size="sm">
          <Copy class="size-4" />
        </Button>
        <Button variant="secondary">
          <Scissors class="size-4" />
        </Button>
        <Button variant="secondary" size="lg">
          <Trash class="size-4" />
        </Button>
        <Button variant="secondary" size="xl">
          <SquarePlus class="size-4" />
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="success" size="sm">
          <Copy class="size-4" />
        </Button>
        <Button variant="success">
          <Scissors class="size-4" />
        </Button>
        <Button variant="success" size="lg">
          <Trash class="size-4" />
        </Button>
        <Button variant="success" size="xl">
          <SquarePlus class="size-4" />
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="danger" size="sm">
          <Copy class="size-4" />
        </Button>
        <Button variant="danger">
          <Scissors class="size-4" />
        </Button>
        <Button variant="danger" size="lg">
          <Trash class="size-4" />
        </Button>
        <Button variant="danger" size="xl">
          <SquarePlus class="size-4" />
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="pending" size="sm">
          <Copy class="size-4" />
        </Button>
        <Button variant="pending">
          <Scissors class="size-4" />
        </Button>
        <Button variant="pending" size="lg">
          <Trash class="size-4" />
        </Button>
        <Button variant="pending" size="xl">
          <SquarePlus class="size-4" />
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button variant="warning" size="sm">
          <Copy class="size-4" />
        </Button>
        <Button variant="warning">
          <Scissors class="size-4" />
        </Button>
        <Button variant="warning" size="lg">
          <Trash class="size-4" />
        </Button>
        <Button variant="warning" size="xl">
          <SquarePlus class="size-4" />
        </Button>
      </div>
    </div>
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" size="sm">
          <Copy class="size-4" />
        </Button>
        <Button look="filled">
          <Scissors class="size-4" />
        </Button>
        <Button look="filled" size="lg">
          <Trash class="size-4" />
        </Button>
        <Button look="filled" size="xl">
          <SquarePlus class="size-4" />
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="primary" size="sm">
          <Copy class="size-4" />
        </Button>
        <Button look="filled" variant="primary">
          <Scissors class="size-4" />
        </Button>
        <Button look="filled" variant="primary" size="lg">
          <Trash class="size-4" />
        </Button>
        <Button look="filled" variant="primary" size="xl">
          <SquarePlus class="size-4" />
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="secondary" size="sm">
          <Copy class="size-4" />
        </Button>
        <Button look="filled" variant="secondary">
          <Scissors class="size-4" />
        </Button>
        <Button look="filled" variant="secondary" size="lg">
          <Trash class="size-4" />
        </Button>
        <Button look="filled" variant="secondary" size="xl">
          <SquarePlus class="size-4" />
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="success" size="sm">
          <Copy class="size-4" />
        </Button>
        <Button look="filled" variant="success">
          <Scissors class="size-4" />
        </Button>
        <Button look="filled" variant="success" size="lg">
          <Trash class="size-4" />
        </Button>
        <Button look="filled" variant="success" size="xl">
          <SquarePlus class="size-4" />
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="danger" size="sm">
          <Copy class="size-4" />
        </Button>
        <Button look="filled" variant="danger">
          <Scissors class="size-4" />
        </Button>
        <Button look="filled" variant="danger" size="lg">
          <Trash class="size-4" />
        </Button>
        <Button look="filled" variant="danger" size="xl">
          <SquarePlus class="size-4" />
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="pending" size="sm">
          <Copy class="size-4" />
        </Button>
        <Button look="filled" variant="pending">
          <Scissors class="size-4" />
        </Button>
        <Button look="filled" variant="pending" size="lg">
          <Trash class="size-4" />
        </Button>
        <Button look="filled" variant="pending" size="xl">
          <SquarePlus class="size-4" />
        </Button>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Button look="filled" variant="warning" size="sm">
          <Copy class="size-4" />
        </Button>
        <Button look="filled" variant="warning">
          <Scissors class="size-4" />
        </Button>
        <Button look="filled" variant="warning" size="lg">
          <Trash class="size-4" />
        </Button>
        <Button look="filled" variant="warning" size="xl">
          <SquarePlus class="size-4" />
        </Button>
      </div>
    </div>
  </div>
</template>
