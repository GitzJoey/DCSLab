<script lang="ts" setup>
import {
  SelectRoot,
  SelectLabel,
  SelectControl,
  SelectTrigger,
  SelectValueText,
  SelectContent,
  SelectItemGroup,
  SelectItemGroupLabel,
  SelectItem,
  SelectItemText,
} from "@/components/ui/select";
import * as select from "@zag-js/select";

const comboboxData = [
  { label: "React", code: "react" },
  { label: "Solid", code: "solid" },
  { label: "Vue", code: "vue" },
  { label: "Svelte", code: "svelte" },
];

const timezoneData = [
  {
    label: "North America",
    items: [
      { value: "est", label: "Eastern Standard Time (EST)" },
      { value: "cst", label: "Central Standard Time (CST)" },
      { value: "mst", label: "Mountain Standard Time (MST)" },
      { value: "pst", label: "Pacific Standard Time (PST)" },
      { value: "akst", label: "Alaska Standard Time (AKST)" },
      { value: "hst", label: "Hawaii Standard Time (HST)" },
    ],
  },
  {
    label: "Europe & Africa",
    items: [
      { value: "gmt", label: "Greenwich Mean Time (GMT)" },
      { value: "cet", label: "Central European Time (CET)" },
      { value: "eet", label: "Eastern European Time (EET)" },
      { value: "west", label: "Western European Summer Time (WEST)" },
      { value: "cat", label: "Central Africa Time (CAT)" },
      { value: "eat", label: "East Africa Time (EAT)" },
    ],
  },
  {
    label: "Asia",
    items: [
      { value: "msk", label: "Moscow Time (MSK)" },
      { value: "ist", label: "India Standard Time (IST)" },
      { value: "cst_china", label: "China Standard Time (CST)" },
      { value: "jst", label: "Japan Standard Time (JST)" },
      { value: "kst", label: "Korea Standard Time (KST)" },
      {
        value: "ist_indonesia",
        label: "Indonesia Central Standard Time (WITA)",
      },
    ],
  },
  {
    label: "Australia & Pacific",
    items: [
      { value: "awst", label: "Australian Western Standard Time (AWST)" },
      { value: "acst", label: "Australian Central Standard Time (ACST)" },
      { value: "aest", label: "Australian Eastern Standard Time (AEST)" },
      { value: "nzst", label: "New Zealand Standard Time (NZST)" },
      { value: "fjt", label: "Fiji Time (FJT)" },
    ],
  },
  {
    label: "South America",
    items: [
      { value: "art", label: "Argentina Time (ART)" },
      { value: "bot", label: "Bolivia Time (BOT)" },
      { value: "brt", label: "Brasilia Time (BRT)" },
      { value: "clt", label: "Chile Standard Time (CLT)" },
    ],
  },
];

const collection = select.collection({
  items: comboboxData,
  itemToValue: (item) => item.label,
});

const collectionTimezone = select.collection({
  items: timezoneData.flatMap((region) =>
    region.items.map((item) => ({
      region: region.label,
      value: item.value,
      label: item.label,
    }))
  ),
});
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <SelectRoot class="w-56" :collection="collection">
          <SelectLabel>Single</SelectLabel>
          <SelectControl>
            <SelectTrigger>
              <SelectValueText placeholder="Select a Framework" />
            </SelectTrigger>
          </SelectControl>
          <SelectContent>
            <SelectItemGroup>
              <SelectItemGroupLabel> Frameworks </SelectItemGroupLabel>
              <SelectItem
                v-for="item in collection.items"
                :key="item.code"
                :item="item"
              >
                <SelectItemText>{{ item.label }}</SelectItemText>
              </SelectItem>
            </SelectItemGroup>
          </SelectContent>
        </SelectRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <SelectRoot class="w-56" :collection="collection" multiple>
          <SelectLabel>Multiple</SelectLabel>
          <SelectControl>
            <SelectTrigger>
              <SelectValueText placeholder="Select a Framework" />
            </SelectTrigger>
          </SelectControl>
          <SelectContent>
            <SelectItemGroup>
              <SelectItemGroupLabel> Frameworks </SelectItemGroupLabel>
              <SelectItem
                v-for="item in collection.items"
                :key="item.code"
                :item="item"
              >
                <SelectItemText>{{ item.label }}</SelectItemText>
              </SelectItem>
            </SelectItemGroup>
          </SelectContent>
        </SelectRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <SelectRoot class="w-56" :collection="collectionTimezone" multiple>
          <SelectLabel>Scrollable</SelectLabel>
          <SelectControl>
            <SelectTrigger>
              <SelectValueText placeholder="Select a Timezone" />
            </SelectTrigger>
          </SelectControl>
          <SelectContent>
            <SelectItemGroup v-for="item in timezoneData" :key="item.label">
              <SelectItemGroupLabel>{{ item.label }}</SelectItemGroupLabel>
              <SelectItem
                v-for="timezoneItem in item.items"
                :key="timezoneItem.value"
                :item="timezoneItem.value"
              >
                <SelectItemText>{{ timezoneItem.label }}</SelectItemText>
              </SelectItem>
            </SelectItemGroup>
          </SelectContent>
        </SelectRoot>
      </div>
    </div>
  </div>
</template>
