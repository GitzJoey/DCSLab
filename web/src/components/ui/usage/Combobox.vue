<script lang="ts" setup>
import {
  ComboboxRoot,
  ComboboxLabel,
  ComboboxControl,
  ComboboxInput,
  ComboboxTrigger,
  ComboboxContent,
  ComboboxItemGroup,
  ComboboxItemGroupLabel,
  ComboboxItem,
  ComboboxItemText,
} from "@/components/ui/combobox";
import { ref, computed } from "vue";
import * as combobox from "@zag-js/combobox";

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

const stateSingle = ref([""]);
const stateMultiple = ref([""]);
const stateTimezone = ref([""]);

const options = ref(comboboxData);
const timezoneOptions = ref(timezoneData);

const collection = computed(() =>
  combobox.collection({
    items: options.value,
    itemToValue: (item) => item.label,
  })
);

const collectionTimezone = computed(() =>
  combobox.collection({
    items: timezoneOptions.value.flatMap((region) =>
      region.items.map((item) => ({
        region: region.label,
        value: item.value,
        label: item.label,
      }))
    ),
  })
);
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <ComboboxRoot
          :collection="collection"
          :value="stateSingle"
          @value-change="(details) => (stateSingle = details.value)"
          @input-value-change="
            ({ inputValue }) => {
              const filtered = comboboxData.filter((item) =>
                item.label.toLowerCase().includes(inputValue.toLowerCase())
              );
              options = filtered.length > 0 ? filtered : comboboxData;
            }
          "
          class="w-56"
        >
          <ComboboxLabel>Single</ComboboxLabel>
          <ComboboxControl>
            <ComboboxTrigger />
          </ComboboxControl>
          <ComboboxContent>
            <ComboboxInput placeholder="Search frameworks..." />
            <ComboboxItemGroup>
              <ComboboxItemGroupLabel>Frameworks</ComboboxItemGroupLabel>
              <ComboboxItem
                v-for="item in collection.items"
                :key="item.code"
                :item="item"
              >
                <ComboboxItemText>{{ item.label }}</ComboboxItemText>
              </ComboboxItem>
            </ComboboxItemGroup>
          </ComboboxContent>
        </ComboboxRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <ComboboxRoot
          :collection="collection"
          :value="stateMultiple"
          @value-change="(details) => (stateMultiple = details.value)"
          @input-value-change="
            ({ inputValue }) => {
              const filtered = comboboxData.filter((item) =>
                item.label.toLowerCase().includes(inputValue.toLowerCase())
              );
              options = filtered.length > 0 ? filtered : comboboxData;
            }
          "
          class="w-56"
          multiple
        >
          <ComboboxLabel>Multiple</ComboboxLabel>
          <ComboboxControl>
            <ComboboxTrigger />
          </ComboboxControl>
          <ComboboxContent>
            <ComboboxInput placeholder="Search frameworks..." />
            <ComboboxItemGroup>
              <ComboboxItemGroupLabel>Frameworks</ComboboxItemGroupLabel>
              <ComboboxItem
                v-for="item in collection.items"
                :key="item.code"
                :item="item"
              >
                <ComboboxItemText>{{ item.label }}</ComboboxItemText>
              </ComboboxItem>
            </ComboboxItemGroup>
          </ComboboxContent>
        </ComboboxRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <ComboboxRoot
          :collection="collection"
          @input-value-change="
            ({ inputValue }) => {
              const filtered = timezoneData
                .map((group) => {
                  const matchedItems = group.items.filter((item) =>
                    item.label.toLowerCase().includes(inputValue.toLowerCase())
                  );

                  return {
                    ...group,
                    items: matchedItems,
                  };
                })
                .filter((group) => group.items.length > 0);

              timezoneOptions = filtered.length > 0 ? filtered : timezoneData;
            }
          "
          :value="stateTimezone"
          @value-change="(details) => (stateTimezone = details.value)"
          class="w-56"
          multiple
        >
          <ComboboxLabel>Scrollable</ComboboxLabel>
          <ComboboxControl>
            <ComboboxTrigger />
          </ComboboxControl>
          <ComboboxContent>
            <ComboboxInput placeholder="Search region..." />
            <ComboboxItemGroup
              v-for="item in timezoneData
                .map((region) => ({
                  ...region,
                  items: region.items.filter((item) =>
                    collectionTimezone.items.some((c) => c.value === item.value)
                  ),
                }))
                .filter((region) => region.items.length > 0)"
              :key="item.label"
            >
              <ComboboxItemGroupLabel>{{ item.label }}</ComboboxItemGroupLabel>
              <ComboboxItem
                v-for="timezoneItem in item.items"
                :key="timezoneItem.value"
                :item="timezoneItem.value"
              >
                <ComboboxItemText>{{ timezoneItem.label }}</ComboboxItemText>
              </ComboboxItem>
            </ComboboxItemGroup>
          </ComboboxContent>
        </ComboboxRoot>
      </div>
    </div>
  </div>
</template>
