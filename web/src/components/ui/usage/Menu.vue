<script lang="ts" setup>
import {
  MenuRoot,
  MenuTrigger,
  MenuPositioner,
  MenuContent,
  MenuItem,
  MenuCheckboxItem,
  MenuSeparator,
  MenuTriggerItem,
  MenuRadioItemGroup,
  MenuItemGroupLabel,
  MenuRadioItem,
} from "@/components/ui/menu";
import { ref } from "vue";
import { Activity, Layout, Zap, MapPin } from "lucide-vue-next";

const react = ref(false);
const solid = ref(false);
const vue = ref(false);
const svelte = ref(false);
const value = ref("React");
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <MenuRoot class="w-56">
          <MenuTrigger>Open menu</MenuTrigger>
          <MenuPositioner>
            <MenuContent>
              <MenuItem value="react"
                ><Activity class="size-4 stroke-1.5" /> React</MenuItem
              >
              <MenuItem value="solid"
                ><Layout class="size-4 stroke-1.5" /> Solid</MenuItem
              >
              <MenuItem value="vue"
                ><Zap class="size-4 stroke-1.5" /> Vue</MenuItem
              >
              <MenuItem value="svelte"
                ><MapPin class="size-4 stroke-1.5" /> Svelte</MenuItem
              >
            </MenuContent>
          </MenuPositioner>
        </MenuRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <MenuRoot class="w-56">
          <MenuTrigger>Open menu</MenuTrigger>
          <MenuPositioner>
            <MenuContent>
              <MenuItem value="react">React</MenuItem>
              <MenuItem value="solid">Solid</MenuItem>
              <MenuItem value="vue">Vue</MenuItem>
              <MenuItem value="svelte">Svelte</MenuItem>
              <MenuSeparator />
              <MenuItem value="react">React</MenuItem>
              <MenuItem value="solid">Solid</MenuItem>
              <MenuItem value="vue">Vue</MenuItem>
              <MenuItem value="svelte">Svelte</MenuItem>
            </MenuContent>
          </MenuPositioner>
        </MenuRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <MenuRoot class="w-56">
          <MenuTrigger>Open menu</MenuTrigger>
          <MenuPositioner>
            <MenuContent>
              <MenuItem shortcut="⇧⌘P" value="react"> React </MenuItem>
              <MenuItem shortcut="⌘B" value="solid"> Solid </MenuItem>
              <MenuItem shortcut="⌘S" value="vue"> Vue </MenuItem>
              <MenuItem shortcut="⌘K" value="svelte"> Svelte </MenuItem>
              <MenuSeparator />
              <MenuItem value="react">React</MenuItem>
              <MenuItem value="solid">Solid</MenuItem>
              <MenuItem value="vue">Vue</MenuItem>
              <MenuItem shortcut="⇧⌘Q" value="svelte"> Svelte </MenuItem>
            </MenuContent>
          </MenuPositioner>
        </MenuRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <MenuRoot class="w-56">
          <MenuTrigger>Open menu</MenuTrigger>
          <MenuPositioner>
            <MenuContent>
              <MenuItem shortcut="⇧⌘P" value="react"> React </MenuItem>
              <MenuItem shortcut="⌘B" value="solid"> Solid </MenuItem>
              <MenuItem shortcut="⌘S" value="vue"> Vue </MenuItem>
              <MenuItem shortcut="⌘K" value="svelte"> Svelte </MenuItem>
              <MenuRoot
                :positioning="{
                  placement: 'right-start',
                  gutter: 12,
                }"
              >
                <MenuTriggerItem>Frameworks</MenuTriggerItem>
                <MenuPositioner>
                  <MenuContent>
                    <MenuItem value="react">React</MenuItem>
                    <MenuItem value="solid">Solid</MenuItem>
                    <MenuItem value="vue">Vue</MenuItem>
                    <MenuItem value="svelte">Svelte</MenuItem>
                  </MenuContent>
                </MenuPositioner>
              </MenuRoot>
              <MenuSeparator />
              <MenuItem disabled value="react"> React </MenuItem>
              <MenuItem value="solid">Solid</MenuItem>
              <MenuItem value="vue">Vue</MenuItem>
              <MenuItem shortcut="⇧⌘Q" value="svelte"> Svelte </MenuItem>
            </MenuContent>
          </MenuPositioner>
        </MenuRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <MenuRoot class="w-56">
          <MenuTrigger>Open menu</MenuTrigger>
          <MenuPositioner>
            <MenuContent>
              <MenuCheckboxItem
                :checked="react"
                :onCheckedChange="(checked) => (react = checked)"
                value="checked"
              >
                React
              </MenuCheckboxItem>
              <MenuCheckboxItem
                :checked="solid"
                :onCheckedChange="(checked) => (solid = checked)"
                value="checked"
              >
                Solid
              </MenuCheckboxItem>
              <MenuCheckboxItem
                :checked="vue"
                :onCheckedChange="(checked) => (vue = checked)"
                value="checked"
              >
                Vue
              </MenuCheckboxItem>
              <MenuCheckboxItem
                :checked="svelte"
                :onCheckedChange="(checked) => (svelte = checked)"
                value="checked"
              >
                Svelte
              </MenuCheckboxItem>
            </MenuContent>
          </MenuPositioner>
        </MenuRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <MenuRoot class="w-56">
          <MenuTrigger>Open menu</MenuTrigger>
          <MenuPositioner>
            <MenuContent>
              <MenuRadioItemGroup>
                <MenuItemGroupLabel>Frameworks</MenuItemGroupLabel>
                <MenuRadioItem
                  v-for="framework in ['React', 'Solid', 'Vue', 'Svelte']"
                  :key="framework"
                  :value="framework"
                  :checked="framework == value"
                  :onCheckedChange="
                    (checked) => (checked ? (value = framework) : '')
                  "
                >
                  {{ framework }}
                </MenuRadioItem>
              </MenuRadioItemGroup>
            </MenuContent>
          </MenuPositioner>
        </MenuRoot>
      </div>
    </div>
  </div>
</template>
