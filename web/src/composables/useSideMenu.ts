import { ref, onMounted, onUnmounted } from 'vue'

export const useSideMenu = () => {
    const compactMenu = ref(localStorage.getItem('compactMenu') === 'true')
    const compactMenuOnHover = ref(false)
    const mobileMenuOpen = ref(false)
    const scrolled = ref(false)

    const toggleCompactMenu = (event: MouseEvent) => {
        event.preventDefault()
        compactMenu.value = !compactMenu.value
        localStorage.setItem('compactMenu', compactMenu.value.toString())
    }

    const onMouseEnterSideMenu = () => {
        compactMenuOnHover.value = true
    }

    const onMouseLeaveSideMenu = () => {
        compactMenuOnHover.value = false
    }

    const openMobileMenu = (event: MouseEvent) => {
        event.preventDefault()
        mobileMenuOpen.value = true
    }

    const closeMobileMenu = (event: MouseEvent) => {
        event.preventDefault()
        mobileMenuOpen.value = false
    }

    const onScrollContent = (event: Event) => {
        const target = event.target as HTMLElement
        scrolled.value = target.scrollTop > 0
    }

    const onResize = () => {
        if (window.innerWidth <= 1600) {
            compactMenu.value = true
            localStorage.setItem('compactMenu', 'true')
        }
    }

    onMounted(() => {
        window.addEventListener('resize', onResize)
        onResize()
    })

    onUnmounted(() => {
        window.removeEventListener('resize', onResize)
    })

    return {
        compactMenu,
        compactMenuOnHover,
        mobileMenuOpen,
        scrolled,
        toggleCompactMenu,
        onMouseEnterSideMenu,
        onMouseLeaveSideMenu,
        openMobileMenu,
        closeMobileMenu,
        onScrollContent,
    }
}
