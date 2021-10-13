import { ref } from 'vue'
import Velocity from 'velocity-animate'

// Toggle mobile menu
const activeMobileMenu = ref(false)
const toggleMobileMenu = () => {
    activeMobileMenu.value = !activeMobileMenu.value
}

// Setup mobile menu
const linkTo = (menu, router) => {
    if (menu.subMenu) {
        menu.activeDropdown = !menu.activeDropdown
    } else {
        activeMobileMenu.value = false
        router.push({
            name: menu.pageName
        })
    }
}

const enter = (el, done) => {
    Velocity(el, 'slideDown', { duration: 300 }, { complete: done })
}

const leave = (el, done) => {
    Velocity(el, 'slideUp', { duration: 300 }, { complete: done })
}

export { activeMobileMenu, toggleMobileMenu, linkTo, enter, leave }
