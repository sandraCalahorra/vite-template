export function retrieveIconList(needComponent = false) {
    /**
     * Load all Vue icon components
     */
    const hiddenIconsModules = import.meta.glob('@/components/core-ui/hidden-icons/*.vue', { eager: true })
    const iconsModules = import.meta.glob('@/components/core-ui/icons/*.vue', { eager: true })

    if (needComponent) {
        const icons = Object.keys(iconsModules).map((key) => {
            const name = key.split('/').pop().replace('.vue', '')

            return {
                name,
                component: iconsModules[key].default
            }
        });

        return icons

    } else {
        const icons = Object.keys(iconsModules).reduce((acc, key) => {
            const name = key.split('/').pop().replace('.vue', '')

            acc[name] = iconsModules[key].default
            return acc
        }, {});

        const hiddenIcons = Object.keys(hiddenIconsModules).reduce((acc, key) => {
            const name = key.split('/').pop().replace('.vue', '')

            acc[name] = hiddenIconsModules[key].default
            return acc
        }, {});

        return { ...icons, ...hiddenIcons }
    }
}
