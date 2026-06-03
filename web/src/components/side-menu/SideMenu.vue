<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Lucide, type Icon } from '@/components/ui/lucide'
import { slideUp, slideDown } from '@/utils/helper'
import { cn } from '@/utils/cn'
import _ from 'lodash'

interface Menu {
  icon?: Icon
  title?: string
  route_name?: string
  params?: any
  badge?: number
  sub_menu?: Menu[]
}

const props = defineProps<{
  menu: (string | Menu)[]
}>()

const route = useRoute()
const router = useRouter()
const formattedMenu = ref<(string | Menu)[]>([])

// State to track open menus based on unique index/path
const openMenus = ref<Record<string, boolean>>({})

const isMenuActive = (menu: string | Menu): boolean => {
  if (typeof menu === 'string') return false
  if (menu.route_name) {
    if (menu.route_name === '/') {
      return route.path === '/'
    }
    return route.name === menu.route_name || route.path.includes(menu.route_name)
  }
  if (menu.sub_menu) {
    return menu.sub_menu.some((sub) => isMenuActive(sub))
  }
  return false
}

const findActiveSubMenus = (subMenu: Menu[]): boolean => {
  return subMenu.some((item) => {
    if (item.route_name) {
      if (item.route_name === '/') {
        return route.path === '/'
      }
      return route.name === item.route_name || route.path.includes(item.route_name)
    }
    if (item.sub_menu) return findActiveSubMenus(item.sub_menu)
    return false
  })
}

const initMenu = (menuList: (string | Menu)[], parentKey = 'menu') => {
  const newOpenMenus: Record<string, boolean> = {}

  const traverse = (list: (string | Menu)[], pk: string) => {
    list.forEach((item, index) => {
      const key = `${pk}-${index}`
      if (typeof item !== 'string' && item.sub_menu) {
        if (findActiveSubMenus(item.sub_menu)) {
          newOpenMenus[key] = true
        }
        traverse(item.sub_menu, key)
      }
    })
  }

  traverse(menuList, parentKey)
  openMenus.value = { ...openMenus.value, ...newOpenMenus }
}

watch(
  [() => props.menu, () => route.name],
  () => {
    formattedMenu.value = props.menu
    initMenu(props.menu)
  },
  { immediate: true },
)

const toggleMenu = (key: string, event: MouseEvent) => {
  event.preventDefault()

  const el = (event.currentTarget as HTMLElement).nextElementSibling as HTMLElement
  if (el) {
    if (openMenus.value[key]) {
      slideUp(el, 300)
      openMenus.value[key] = false
    } else {
      slideDown(el, 300)
      openMenus.value[key] = true
    }
  }
}

const linkTo = (menu: Menu, event: MouseEvent) => {
  event.preventDefault()
  if (menu.route_name) {
    router.push({
      name: menu.route_name,
      params: menu.params,
    })
  }
}
</script>

<template>
  <ul class="scrollable">
    <template v-for="(menu, menuKey) in formattedMenu" :key="menuKey">
      <!-- BEGIN: First Child -->
      <li v-if="typeof menu === 'string'" class="side-menu__group-label">
        {{ menu }}
      </li>
      <li v-else>
        <a
          href=""
          @click="
            (event) => {
              if (menu.sub_menu) {
                toggleMenu(`menu-${menuKey}`, event)
              } else {
                linkTo(menu, event)
              }
            }
          "
          :class="
            cn(
              'side-menu__link',
              isMenuActive(menu) ? 'side-menu__link--active' : '',
            )
          "
        >
          <Lucide
            v-if="menu.icon"
            class="side-menu__link__icon"
            :icon="menu.icon"
          />
          <div class="side-menu__link__title">{{ menu.title }}</div>
          <div v-if="menu.badge" class="side-menu__link__badge">
            {{ menu.badge }}
          </div>
          <Lucide
            v-if="menu.sub_menu"
            :class="
              cn(
                'side-menu__link__chevron transition-transform duration-300',
                openMenus[`menu-${menuKey}`] ? 'rotate-180' : '',
              )
            "
            icon="ChevronDown"
          />
        </a>
        <!-- BEGIN: Second Child -->
        <ul
          v-if="menu.sub_menu"
          :class="cn('hidden', { block: openMenus[`menu-${menuKey}`] })"
        >
          <li v-for="(subMenu, subMenuKey) in menu.sub_menu" :key="subMenuKey">
            <a
              href=""
              @click="
                (event) => {
                  if (subMenu.sub_menu) {
                    toggleMenu(`menu-${menuKey}-${subMenuKey}`, event)
                  } else {
                    linkTo(subMenu, event)
                  }
                }
              "
              :class="
                cn(
                  'side-menu__link',
                  isMenuActive(subMenu) ? 'side-menu__link--active' : '',
                )
              "
            >
              <Lucide
                v-if="subMenu.icon"
                class="side-menu__link__icon"
                :icon="subMenu.icon"
              />
              <div class="side-menu__link__title">
                {{ subMenu.title }}
              </div>
              <div v-if="subMenu.badge" class="side-menu__link__badge">
                {{ subMenu.badge }}
              </div>
              <Lucide
                v-if="subMenu.sub_menu"
                :class="
                  cn(
                    'side-menu__link__chevron transition-transform duration-300',
                    openMenus[`menu-${menuKey}-${subMenuKey}`] ? 'rotate-180' : '',
                  )
                "
                icon="ChevronDown"
              />
            </a>
            <!-- BEGIN: Third Child -->
            <ul
              v-if="subMenu.sub_menu"
              :class="
                cn('hidden', { block: openMenus[`menu-${menuKey}-${subMenuKey}`] })
              "
            >
              <li
                v-for="(lastSubMenu, lastSubMenuKey) in subMenu.sub_menu"
                :key="lastSubMenuKey"
              >
                <a
                  href=""
                  @click.prevent="linkTo(lastSubMenu, $event)"
                  :class="
                    cn(
                      'side-menu__link',
                      isMenuActive(lastSubMenu) ? 'side-menu__link--active' : '',
                    )
                  "
                >
                  <div class="side-menu__link__icon">
                    <Lucide v-if="lastSubMenu.icon" :icon="lastSubMenu.icon" />
                  </div>
                  <div class="side-menu__link__title">
                    {{ lastSubMenu.title }}
                  </div>
                  <div v-if="lastSubMenu.badge" class="side-menu__link__badge">
                    {{ lastSubMenu.badge }}
                  </div>
                </a>
              </li>
            </ul>
            <!-- END: Third Child -->
          </li>
        </ul>
        <!-- END: Second Child -->
      </li>
      <!-- END: First Child -->
    </template>
  </ul>
</template>


