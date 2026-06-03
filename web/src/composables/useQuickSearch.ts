import { ref, onMounted, onUnmounted } from 'vue'

export const useQuickSearch = () => {
    const quickSearchDialogOpen = ref(false)

    const handleKeyDown = (event: KeyboardEvent) => {
        if (event.ctrlKey || event.metaKey) {
            if (event.key === 'k') {
                event.preventDefault()
                quickSearchDialogOpen.value = true
            }
        }

        if (event.key === 'Escape') {
            quickSearchDialogOpen.value = false
        }
    }

    onMounted(() => {
        window.addEventListener('keydown', handleKeyDown)
    })

    onUnmounted(() => {
        window.removeEventListener('keydown', handleKeyDown)
    })

    return {
        quickSearchDialogOpen,
    }
}
