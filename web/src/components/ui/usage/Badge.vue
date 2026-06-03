<script lang="ts" setup>
import { ChevronDown, CheckSquare } from "lucide-vue-next";
import { Badge } from "@/components/ui/badge";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Badge variant="primary">12%</Badge>
        <Badge variant="secondary">12%</Badge>
        <Badge variant="success">12%</Badge>
        <Badge variant="danger">12%</Badge>
        <Badge variant="pending">12%</Badge>
        <Badge variant="warning">12%</Badge>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Badge variant="primary"> 12% <ChevronDown /> </Badge>
        <Badge variant="secondary"> 12% <ChevronDown /> </Badge>
        <Badge variant="success"> 12% <ChevronDown /> </Badge>
        <Badge variant="danger"> 12% <ChevronDown /> </Badge>
        <Badge variant="pending"> 12% <ChevronDown /> </Badge>
        <Badge variant="warning"> 12% <ChevronDown /> </Badge>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Badge look="outline" variant="primary"> 12% </Badge>
        <Badge look="outline" variant="secondary"> 12% </Badge>
        <Badge look="outline" variant="success"> 12% </Badge>
        <Badge look="outline" variant="danger"> 12% </Badge>
        <Badge look="outline" variant="pending"> 12% </Badge>
        <Badge look="outline" variant="warning"> 12% </Badge>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Badge look="outline" variant="primary"> 12% <ChevronDown /> </Badge>
        <Badge look="outline" variant="secondary"> 12% <ChevronDown /> </Badge>
        <Badge look="outline" variant="success"> 12% <ChevronDown /> </Badge>
        <Badge look="outline" variant="danger"> 12% <ChevronDown /> </Badge>
        <Badge look="outline" variant="pending"> 12% <ChevronDown /> </Badge>
        <Badge look="outline" variant="warning"> 12% <ChevronDown /> </Badge>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Badge look="filled" variant="primary"> 12% </Badge>
        <Badge look="filled" variant="secondary"> 12% </Badge>
        <Badge look="filled" variant="success"> 12% </Badge>
        <Badge look="filled" variant="danger"> 12% </Badge>
        <Badge look="filled" variant="pending"> 12% </Badge>
        <Badge look="filled" variant="warning"> 12% </Badge>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Badge look="filled" variant="primary"> 12% <ChevronDown /> </Badge>
        <Badge look="filled" variant="secondary"> 12% <ChevronDown /> </Badge>
        <Badge look="filled" variant="success"> 12% <ChevronDown /> </Badge>
        <Badge look="filled" variant="danger"> 12% <ChevronDown /> </Badge>
        <Badge look="filled" variant="pending"> 12% <ChevronDown /> </Badge>
        <Badge look="filled" variant="warning"> 12% <ChevronDown /> </Badge>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Badge
          look="outline"
          variant="primary"
          content="12% Higher than last month"
        >
          <CheckSquare /> 12%
        </Badge>
        <Badge
          look="outline"
          variant="secondary"
          content="12% Higher than last month"
        >
          <CheckSquare /> 12%
        </Badge>
        <Badge
          look="outline"
          variant="success"
          content="12% Higher than last month"
        >
          <CheckSquare /> 12%
        </Badge>
        <Badge
          look="outline"
          variant="danger"
          content="12% Higher than last month"
        >
          <CheckSquare /> 12%
        </Badge>
        <Badge
          look="outline"
          variant="pending"
          content="12% Higher than last month"
        >
          <CheckSquare /> 12%
        </Badge>
        <Badge
          look="outline"
          variant="warning"
          content="12% Higher than last month"
        >
          <CheckSquare /> 12%
        </Badge>
      </div>
    </div>
  </div>
</template>
