<template>
    <div :class="wrapperClasses">
        <div :class="containerClasses">
            <div :class="panelClasses" :style="panelStyles" :role="role">
                <div :class="bodyClasses">
                    <span
                        v-if="showIcon && (resolvedIcon || $slots.icon)"
                        :class="iconWrapperClasses"
                        :style="iconStyles"
                    >
                        <slot name="icon">
                            <component :is="resolvedIcon" v-if="resolvedIcon" :class="iconClasses" />
                        </slot>
                    </span>

                    <div class="min-w-0 flex-1">
                        <div v-if="hasTitle" :class="titleClasses">
                            <slot name="title">
                                <span v-html="title" />
                            </slot>
                        </div>

                        <div :class="messageClasses">
                            <slot name="message">
                                <slot>
                                    <span v-html="message" />
                                </slot>
                            </slot>
                        </div>

                        <div v-if="hasListContent" :class="listContainerClasses">
                            <slot name="list">
                                <ul :class="listClasses">
                                    <li
                                        v-for="(item, index) in listItems"
                                        :key="index"
                                        v-html="item"
                                    />
                                </ul>
                            </slot>
                        </div>

                        <div v-if="$slots.actions" :class="actionsClasses">
                            <slot name="actions" />
                        </div>

                        <div
                            v-if="showNavigation"
                            :class="navigationClasses"
                        >
                            <button
                                v-if="hasPrev"
                                type="button"
                                :class="navButtonClasses"
                                :style="closeStyles"
                                @click="$emit('prev')"
                                title="Anterior"
                            >
                                <component :is="'IconChevronLeft'" class="size-4" />
                            </button>
                            <div v-else-if="hasNext" class="w-7" />

                            <div v-if="counter" class="text-[11px] font-semibold tracking-[0.14em] uppercase">
                                {{ counter }}
                            </div>

                            <button
                                v-if="hasNext"
                                type="button"
                                :class="navButtonClasses"
                                :style="closeStyles"
                                @click="$emit('next')"
                                title="Siguiente"
                            >
                                <component :is="'IconChevronRight'" class="size-4" />
                            </button>
                            <div v-else-if="hasPrev" class="w-7" />
                        </div>
                    </div>

                    <button
                        v-if="closable"
                        type="button"
                        :class="closeButtonClassesResolved"
                        :style="closeStyles"
                        @click="emitClose"
                    >
                        <slot name="close-icon">
                            <component
                                :is="closeIcon || 'IconXSolid'"
                                :class="closeIconClasses"
                            />
                        </slot>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
/*
|--------------------------------------------------------------------------
| InfoModal
|--------------------------------------------------------------------------
| Componente de alertas inspirado en Flowbite.
|
| Uso rápido:
| <InfoModal type="info" message="Mensaje simple" />
| <InfoModal type="danger" variant="bordered" title="Error" closable />
| <InfoModal type="success" variant="filled" :list-items="['Uno', 'Dos']" />
|
| Tipos disponibles:
| - info
| - success
| - warning
| - important
| - danger
| - dark
| - announcement
|
| Variantes disponibles:
| - soft: fondo suave, sin borde marcado
| - bordered: fondo suave + borde
| - outline: fondo blanco + borde
| - filled: fondo sólido
|
| Opciones principales:
| - title: título superior
| - message: contenido principal HTML
| - listItems: lista de elementos
| - closable: muestra botón de cierre
| - showIcon: muestra / oculta icono
| - color: color base rápido
| - palette: override completo de colores
| - size: sm | md | lg
| - layout: inline | stacked
| - rounded: none | sm | md | lg | xl | pill
| - fullWidth: fuerza ancho completo
|
| Slots:
| - default / message
| - title
| - icon
| - list
| - actions
| - close-icon
|
| Compatibilidad:
| Mantiene soporte para blocking, includeOverlay, counter, hasPrev y hasNext.
*/
const TYPE_PALETTES = {
    info: {
        text: "#1e429f",
        muted: "#1d4ed8",
        icon: "#1d4ed8",
        background: "#eff6ff",
        border: "#bfdbfe",
        hover: "#dbeafe",
        filledBackground: "#1d4ed8",
        filledText: "#ffffff",
    },
    success: {
        text: "#166534",
        muted: "#16a34a",
        icon: "#16a34a",
        background: "#ecfdf3",
        border: "#bbf7d0",
        hover: "#dcfce7",
        filledBackground: "#16a34a",
        filledText: "#ffffff",
    },
    warning: {
        text: "#92400e",
        muted: "#d97706",
        icon: "#d97706",
        background: "#fff7ed",
        border: "#fed7aa",
        hover: "#ffedd5",
        filledBackground: "#d97706",
        filledText: "#ffffff",
    },
    important: {
        text: "#9f1239",
        muted: "#e11d48",
        icon: "#e11d48",
        background: "#fff1f2",
        border: "#fecdd3",
        hover: "#ffe4e6",
        filledBackground: "#e11d48",
        filledText: "#ffffff",
    },
    danger: {
        text: "#b42318",
        muted: "#dc2626",
        icon: "#dc2626",
        background: "#fef3f2",
        border: "#fecaca",
        hover: "#fee2e2",
        filledBackground: "#dc2626",
        filledText: "#ffffff",
    },
    dark: {
        text: "#111827",
        muted: "#374151",
        icon: "#111827",
        background: "#f3f4f6",
        border: "#d1d5db",
        hover: "#e5e7eb",
        filledBackground: "#111827",
        filledText: "#ffffff",
    },
    announcement: {
        text: "#9a3412",
        muted: "#c2410c",
        icon: "#c2410c",
        background: "#fff7ed",
        border: "#fed7aa",
        hover: "#ffedd5",
        filledBackground: "var(--primary, #E47209)",
        filledText: "#ffffff",
    },
};

const SIZE_MAP = {
    sm: {
        panel: "px-3 py-2.5 text-xs",
        gap: "gap-2.5",
        iconWrapper: "size-3.5 min-w-3.5",
        icon: "size-3.5",
        close: "size-7",
        closeIcon: "size-3.5",
        title: "text-xs",
        message: "text-xs",
        actions: "mt-3",
        list: "mt-2",
    },
    md: {
        panel: "px-4 py-2 text-sm",
        gap: "gap-3",
        iconWrapper: "size-4 min-w-4",
        icon: "size-4",
        close: "size-8",
        closeIcon: "size-4",
        title: "text-sm",
        message: "text-sm",
        actions: "mt-3.5",
        list: "mt-2.5",
    },
    lg: {
        panel: "px-5 py-4 text-sm",
        gap: "gap-4",
        iconWrapper: "size-5 min-w-5",
        icon: "size-5",
        close: "size-9",
        closeIcon: "size-4",
        title: "text-base",
        message: "text-sm",
        actions: "mt-4",
        list: "mt-3",
    },
};

export default {
    name: "InfoModal",

    props: {
        // Contenido principal
        message: { type: String, default: "" },
        title: { type: String, default: "" },

        // Estilo base
        type: {
            type: String,
            default: "info",
            validator: (v) => ["info", "success", "warning", "important", "danger", "dark", "announcement"].includes(v),
        },
        variant: {
            type: String,
            default: null,
            validator: (v) => v === null || ["soft", "bordered", "outline", "filled"].includes(v),
        },
        layout: {
            type: String,
            default: null,
            validator: (v) => v === null || ["inline", "stacked"].includes(v),
        },
        size: {
            type: String,
            default: "md",
            validator: (v) => ["sm", "md", "lg"].includes(v),
        },

        // Personalización visual
        color: { type: String, default: null },
        palette: { type: Object, default: () => ({}) },
        icon: { type: [String, Object], default: null },
        showIcon: { type: Boolean, default: true },
        closable: { type: Boolean, default: false },
        closeIcon: { type: [String, Object], default: null },
        fullWidth: { type: Boolean, default: false },
        listItems: { type: Array, default: () => [] },
        blocking: { type: Boolean, default: false },
        counter: { type: String, default: null },
        includeOverlay: { type: Boolean, default: true },
        hasPrev: { type: Boolean, default: false },
        hasNext: { type: Boolean, default: false },
        rounded: {
            type: String,
            default: "md",
            validator: (v) => ["none", "sm", "md", "lg", "xl", "pill"].includes(v),
        },
        role: { type: String, default: "alert" },

        // Overrides de clases para ajustar el componente desde fuera sin tocarlo
        wrapperClass: { type: [String, Array, Object], default: null },
        containerClass: { type: [String, Array, Object], default: null },
        panelClass: { type: [String, Array, Object], default: null },
        bodyClass: { type: [String, Array, Object], default: null },
        titleClass: { type: [String, Array, Object], default: null },
        messageClass: { type: [String, Array, Object], default: null },
        iconWrapperClass: { type: [String, Array, Object], default: null },
        iconClass: { type: [String, Array, Object], default: null },
        listClass: { type: [String, Array, Object], default: null },
        actionsClass: { type: [String, Array, Object], default: null },
        closeButtonClass: { type: [String, Array, Object], default: null },
    },

    emits: ["close", "prev", "next"],

    computed: {
        sizeConfig() {
            return SIZE_MAP[this.size] || SIZE_MAP.md;
        },

        roundedClass() {
            const roundedClasses = {
                none: "rounded-none",
                sm: "rounded",
                md: "rounded-md",
                lg: "rounded-lg",
                xl: "rounded-xl",
                pill: "rounded-full",
            };

            return roundedClasses[this.rounded] || roundedClasses.md;
        },

        hasTitle() {
            return !!this.title || !!this.$slots.title;
        },

        hasListContent() {
            return this.listItems.length > 0 || !!this.$slots.list;
        },

        hasAdditionalContent() {
            return this.hasTitle || this.hasListContent || !!this.$slots.actions || this.showNavigation;
        },

        showNavigation() {
            return !!this.counter || this.hasPrev || this.hasNext;
        },

        resolvedVariant() {
            if (this.variant) return this.variant;
            if (this.type === "announcement" || this.blocking || this.hasAdditionalContent) return "bordered";
            return "soft";
        },

        resolvedLayout() {
            if (this.layout) return this.layout;
            if (this.blocking || this.hasAdditionalContent) return "stacked";
            return "inline";
        },

        resolvedPalette() {
            const basePalette = { ...(TYPE_PALETTES[this.type] || TYPE_PALETTES.info) };

            if (this.color) {
                return {
                    ...basePalette,
                    ...this.palette,
                    ...this.buildPaletteFromColor(this.color),
                };
            }

            return {
                ...basePalette,
                ...this.palette,
            };
        },

        resolvedIcon() {
            if (this.icon) return this.icon;

            const typeIcons = {
                info: "IconInfo",
                success: "IconCheckmarkCircleSolid",
                warning: "IconWarningSolid",
                important: "IconErrorCircleSolid",
                danger: "IconDangerSolid",
                dark: "IconInfo",
                announcement: "IconAnnouncement",
            };

            return typeIcons[this.type] || null;
        },

        wrapperClasses() {
            return [
                this.blocking
                    ? (this.includeOverlay
                        ? "fixed inset-0 z-[999] flex items-center justify-center p-2 bg-black/60 backdrop-blur-sm pointer-events-auto"
                        : "w-full max-w-3xl")
                    : (this.fullWidth || this.type === "announcement" ? "w-full" : "max-w-full"),
                this.wrapperClass,
            ];
        },

        containerClasses() {
            return [
                this.blocking
                    ? "w-full max-w-3xl"
                    : (this.fullWidth || this.type === "announcement" ? "w-full" : "inline-flex max-w-full"),
                this.containerClass,
            ];
        },

        panelClasses() {
            return [
                "info-modal__panel relative border shadow-sm transition-colors duration-200",
                this.sizeConfig.panel,
                this.roundedClass,
                this.blocking ? "w-full shadow-xl" : "w-full",
                this.panelClass,
            ];
        },

        bodyClasses() {
            return [
                "flex min-w-0",
                this.sizeConfig.gap,
                this.resolvedLayout === "stacked" ? "items-start" : "items-start sm:items-center",
                this.bodyClass,
            ];
        },

        iconWrapperClasses() {
            return [
                "inline-flex shrink-0 items-center justify-center",
                this.sizeConfig.iconWrapper,
                this.resolvedLayout === "stacked" ? "mt-0.5" : "",
                this.iconWrapperClass,
            ];
        },

        iconClasses() {
            return [
                this.sizeConfig.icon,
                this.iconClass,
            ];
        },

        titleClasses() {
            return [
                "mb-1.5 font-semibold leading-5",
                this.sizeConfig.title,
                this.titleClass,
            ];
        },

        messageClasses() {
            return [
                "info-modal__message leading-6 break-words",
                this.sizeConfig.message,
                this.messageClass,
            ];
        },

        listContainerClasses() {
            return [
                this.sizeConfig.list,
            ];
        },

        listClasses() {
            return [
                "info-modal__list list-disc space-y-1 ps-5 text-sm",
                this.listClass,
            ];
        },

        actionsClasses() {
            return [
                "flex flex-wrap items-center gap-2",
                this.sizeConfig.actions,
                this.actionsClass,
            ];
        },

        navigationClasses() {
            return [
                "mt-4 flex items-center justify-center gap-4 border-t pt-3 text-[11px] font-medium",
                this.resolvedVariant === "filled" ? "border-white/20" : "border-current/10",
            ];
        },

        navButtonClasses() {
            return [
                "info-modal__nav-button inline-flex items-center justify-center rounded-md transition-colors",
                this.sizeConfig.close,
            ];
        },

        closeButtonClassesResolved() {
            return [
                "info-modal__close inline-flex shrink-0 items-center justify-center rounded-md transition-colors",
                this.sizeConfig.close,
                this.closable && this.resolvedLayout === "stacked" ? "self-start" : "self-start sm:self-center",
                this.closeButtonClass,
            ];
        },

        closeIconClasses() {
            return this.sizeConfig.closeIcon;
        },

        panelStyles() {
            const palette = this.resolvedPalette;
            const variantStyles = this.getVariantStyles(palette);

            return {
                "--info-modal-hover": palette.hover,
                "--info-modal-text": variantStyles.textColor,
                "--info-modal-muted": variantStyles.mutedColor,
                "--info-modal-border": variantStyles.borderColor,
                "--info-modal-bg": variantStyles.backgroundColor,
                "--info-modal-icon": variantStyles.iconColor,
                backgroundColor: variantStyles.backgroundColor,
                borderColor: variantStyles.borderColor,
                color: variantStyles.textColor,
            };
        },

        iconStyles() {
            return {
                color: this.getVariantStyles(this.resolvedPalette).iconColor,
            };
        },

        closeStyles() {
            return {
                color: this.getVariantStyles(this.resolvedPalette).textColor,
            };
        },
    },

    methods: {
        emitClose() {
            this.$emit("close");
        },

        buildPaletteFromColor(color) {
            const rgb = this.hexToRgb(color);

            if (!rgb) {
                return {
                    text: color,
                    muted: color,
                    icon: color,
                    background: `color-mix(in srgb, ${color} 10%, white)`,
                    border: `color-mix(in srgb, ${color} 28%, white)`,
                    hover: `color-mix(in srgb, ${color} 18%, white)`,
                    filledBackground: color,
                    filledText: "#ffffff",
                };
            }

            return {
                text: color,
                muted: color,
                icon: color,
                background: `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.12)`,
                border: `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.32)`,
                hover: `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.18)`,
                filledBackground: color,
                filledText: this.getReadableTextColor(rgb),
            };
        },

        getVariantStyles(palette) {
            switch (this.resolvedVariant) {
                case "filled":
                    return {
                        backgroundColor: palette.filledBackground,
                        borderColor: palette.filledBackground,
                        textColor: palette.filledText,
                        mutedColor: palette.filledText,
                        iconColor: palette.filledText,
                    };
                case "outline":
                    return {
                        backgroundColor: "#ffffff",
                        borderColor: palette.border,
                        textColor: palette.text,
                        mutedColor: palette.muted,
                        iconColor: palette.icon,
                    };
                case "bordered":
                    return {
                        backgroundColor: palette.background,
                        borderColor: palette.border,
                        textColor: palette.text,
                        mutedColor: palette.muted,
                        iconColor: palette.icon,
                    };
                case "soft":
                default:
                    return {
                        backgroundColor: palette.background,
                        borderColor: "transparent",
                        textColor: palette.text,
                        mutedColor: palette.muted,
                        iconColor: palette.icon,
                    };
            }
        },

        hexToRgb(color) {
            if (!color || typeof color !== "string") {
                return null;
            }

            let hex = color.trim().replace("#", "");

            if (hex.length === 3) {
                hex = hex.split("").map((char) => char + char).join("");
            }

            if (!/^[0-9A-Fa-f]{6}$/.test(hex)) {
                return null;
            }

            return {
                r: parseInt(hex.slice(0, 2), 16),
                g: parseInt(hex.slice(2, 4), 16),
                b: parseInt(hex.slice(4, 6), 16),
            };
        },

        getReadableTextColor({ r, g, b }) {
            const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
            return luminance > 0.6 ? "#111827" : "#ffffff";
        },
    },
};
</script>

<style scoped>
.info-modal__panel {
    color: var(--info-modal-text);
}

.info-modal__message,
.info-modal__list,
.info-modal__nav-button,
.info-modal__close {
    color: inherit;
}

.info-modal__message :deep(a),
.info-modal__list :deep(a) {
    color: inherit;
    text-decoration: underline;
    text-underline-offset: 2px;
    font-weight: 600;
}

.info-modal__message :deep(a:hover),
.info-modal__list :deep(a:hover) {
    opacity: 0.85;
}

.info-modal__message :deep(strong),
.info-modal__list :deep(strong) {
    font-weight: 700;
}

.info-modal__close:hover,
.info-modal__nav-button:hover {
    background-color: var(--info-modal-hover);
}
</style>
