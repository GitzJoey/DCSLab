import Velocity from 'velocity-animate'

const findActiveMenu = (subMenu, route) => {
    let match = false
    subMenu.forEach(item => {
        if (item.pageName === route.name && !item.ignore) {
            match = true
        } else if (!match && item.subMenu) {
            match = findActiveMenu(item.subMenu, route)
        }
    })
    return match
}

const nestedMenu = (menu, route) => {
    menu.forEach((item, key) => {
        if (typeof item !== 'string') {
            let menuItem = menu[key]
            menuItem.active =
                (item.pageName === route.name ||
                    (item.subMenu && findActiveMenu(item.subMenu, route))) &&
                !item.ignore

            if (item.subMenu) {
                menuItem.activeDropdown = findActiveMenu(item.subMenu, route)
                menuItem = {
                    ...item,
                    ...nestedMenu(item.subMenu, route)
                }
            }
        }
    })

    return menu
}

const linkTo = (menu, router, event) => {
    if (menu.subMenu) {
        menu.activeDropdown = !menu.activeDropdown
    } else {
        event.preventDefault()
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

export { nestedMenu, linkTo, enter, leave }
