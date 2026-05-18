<template>
    <div v-if="blocking" :class="includeOverlay ? 'fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm pointer-events-auto' : 'w-full max-w-2xl'">
        <div
            :class="[
                'flex flex-col gap-4 p-6 rounded-[.25rem] border bg-white shadow-2xl relative w-full'
            ]"
            :style="{
                borderColor: resolvedColor,
            }"
        >
             <!-- Close button for blocking-->
            <button
                v-if="closable"
                type="button"
                class="absolute top-3 right-3 p-1 rounded-full hover:bg-gray-100 transition flex items-center justify-center shrink-0"
                @click="emitClose"
                :style="{ color: resolvedColor }"
            >
                <component
                    :is="closeIcon || 'IconXSolid'"
                    class="w-4 h-4"
                />
            </button>

            <div class="flex gap-4 items-start">
                <span
                    v-if="showIcon"
                    class="w-8 h-8 flex items-center justify-center shrink-0 pt-1 scale-x-[-1]"
                    :style="{ color: resolvedColor }"
                >
                    <component :is="resolvedIcon" v-if="resolvedIcon" class="w-6 h-6" />
                </span>

                <div class="flex flex-col gap-2 flex-1 pr-6">
                    <div class="break-words">
                        <slot name="message">
                            <span v-html="message" />
                        </slot>
                    </div>

                    <div
                        v-if="counter"
                        class="flex items-center justify-center gap-6 pt-4 mt-auto border-t border-gray-100 select-none"
                    >
                        <!-- Prev Button -->
                        <button
                            v-if="hasPrev"
                            type="button"
                            class="p-1 rounded-full hover:bg-gray-100 transition-colors flex items-center justify-center"
                            @click="$emit('prev')"
                            title="Anuncio anterior"
                        >
                            <component :is="'IconChevronLeft'" class="w-5 h-5" :style="{ color: resolvedColor }" />
                        </button>
                        <div v-else-if="hasNext" class="w-7"></div>

                        <!-- Counter -->
                        <div
                            class="text-xs font-semibold tracking-widest uppercase"
                            :style="{ color: resolvedColor }"
                        >
                            {{ counter }}
                        </div>

                        <!-- Next Button -->
                        <button
                            v-if="hasNext"
                            type="button"
                            class="p-1 rounded-full hover:bg-gray-100 -rotate-90 transition-colors flex items-center justify-center"
                            @click="$emit('next')"
                            title="Siguiente anuncio"
                        >
                            <component :is="'IconChevronRight'" class="w-5 h-5" :style="{ color: resolvedColor }" />
                        </button>
                        <div v-else-if="hasPrev" class="w-7"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div
        v-else
        :class="[
            'flex items-center py-1 px-2 rounded-[.25rem] border text-sm italic max-w-full',
            type !== 'announcement' ? 'w-fit' : 'w-full'
        ]"
        :style="{
            borderColor: resolvedColor,
            backgroundColor: type !== 'announcement' ? bgColor : '#fff',
            color: resolvedColor,
        }"
    >
        <div
            :class="[
                'flex gap-2 flex-1 min-w-0',
                type === 'announcement'
                ? 'items-start'
                : 'items-center'
            ]"
        >
            <!-- Principal icon -->
            <span
                v-if="showIcon"
                :class="[
                    'w-5 min-w-5 flex mt-0.5',
                    type === 'announcement'
                    ? 'items-start justify-start scale-x-[-1]'
                    : 'items-center justify-center'
                ]"
            >
                <component :is="resolvedIcon" v-if="resolvedIcon" />
            </span>

            <!-- Message -->
            <span class="break-words">
                <slot name="message">
                    <span v-html="message" />
                </slot>
            </span>
        </div>

        <!-- Close button -->
        <button
            v-if="closable"
            type="button"
            class="ml-3 p-0.5 rounded-full hover:bg-gray-200/40 transition flex items-center justify-center shrink-0"
            @click="emitClose"
            :style="{ color: resolvedColor }"
        >
            <component
                :is="closeIcon || 'IconXSolid'"
                class="w-3.5 h-3.5"
            />
        </button>
    </div>
</template>

<script>
export default {
    name: "InfoModal",

    props: {
        message: { type: String, default: "" },
        type: {
            type: String,
            default: "info",
            validator: (v) => ["info", "success", "warning", "danger", "announcement"].includes(v),
        },
        color: { type: String, default: null },
        icon: { type: [String, Object], default: null },
        showIcon: { type: Boolean, default: true },
        closable: { type: Boolean, default: false },
        closeIcon: { type: [String, Object], default: null },
        blocking: { type: Boolean, default: false },
        counter: { type: String, default: null },
        includeOverlay: { type: Boolean, default: true },
        hasPrev: { type: Boolean, default: false },
        hasNext: { type: Boolean, default: false },
    },

    emits: ["close", "prev", "next"],

    computed: {
        resolvedColor() {
            if (this.color) return this.color;
            const typeColors = {
                info: "#444444",
                success: "#16a34a",
                warning: "#ca8a04",
                danger: "#dc2626",
                announcement: "var(--primary)",
            };

            return typeColors[this.type] || "#6b7280";
        },

        bgColor() {
            if (this.resolvedColor.startsWith("#")) {
                const hex = this.resolvedColor.replace("#", "");
                const r = parseInt(hex.substring(0, 2), 16);
                const g = parseInt(hex.substring(2, 4), 16);
                const b = parseInt(hex.substring(4, 6), 16);
                return `rgba(${r}, ${g}, ${b}, 0.1)`;
            }

            return this.resolvedColor + "20";
        },

        resolvedIcon() {
            if (this.icon) return this.icon;
            const typeIcons = {
                info: "IconInfo",
                success: "IconCheckmarkCircleSolid",
                warning: "IconWarningSolid",
                danger: "IconDangerSolid",
                announcement: "IconAnnouncement",
            };

            return typeIcons[this.type] || null;
        },
    },

    methods: {
        emitClose() {
            this.$emit("close");
        },
    },
};
</script>
