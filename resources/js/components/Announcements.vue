
<template>
    <div
        v-if="nonDismissedAnnouncements.length > 0"
        class="absolute top-0 h-fit flex flex-col z-[100] w-full max-w-full gap-2 p-4 pointer-events-none"
    >
        <!-- Backdrop Persistent (only for blocking) -->
        <transition name="fade">
            <div
                v-if="currentBlockingAnnouncement"
                class="fixed inset-0 z-[998] bg-black/60 backdrop-blur-sm pointer-events-auto"
            ></div>
        </transition>

        <!-- Normal announcements loop -->
        <transition-group
            name="fade-slide"
            tag="div"
            class="flex flex-col gap-2 pointer-events-auto"
        >
            <InfoModal
                v-for="announcement in normalAnnouncements"
                :key="announcement.id"
                type="announcement"
                closable
                @close="$emit('close', announcement.id)"
            >
                <template #message>
                    <span>
                        <strong v-if="announcement.title" class="font-medium" v-html="announcement.title"></strong>:
                        <span v-html="announcement.content"></span>
                    </span>
                </template>
            </InfoModal>
        </transition-group>

        <!-- Blocking Announcement Card (Sequential) -->
        <div
            v-if="currentBlockingAnnouncement"
            class="fixed inset-0 z-[999] flex items-center justify-center p-4 pointer-events-none"
        >
            <transition name="modal-scale" mode="out-in">
                <InfoModal
                    :key="currentBlockingAnnouncement.id"
                    type="announcement"
                    blocking
                    :include-overlay="false"
                    :counter="blockingCounter"
                    :has-prev="currentIndex > 0"
                    :has-next="currentIndex < blockingAnnouncements.length - 1"
                    closable
                    class="pointer-events-auto"
                    @close="handleClose(currentBlockingAnnouncement.id)"
                    @prev="prevAnnouncement"
                    @next="nextAnnouncement"
                >
                    <template #message>
                        <div class="flex flex-col gap-1">
                            <strong v-if="currentBlockingAnnouncement.title" class="font-bold text-base text-gray-900" v-html="currentBlockingAnnouncement.title"></strong>
                            <span v-html="currentBlockingAnnouncement.content"></span>
                        </div>
                    </template>
                </InfoModal>
            </transition>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Announcements',
    props: {
        announcements: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            currentIndex: 0
        }
    },
    watch: {
        blockingAnnouncements(newVal) {
            // Reset index if we have fewer announcements than the current index
            if (this.currentIndex >= newVal.length) {
                this.currentIndex = Math.max(0, newVal.length - 1);
            }
        }
    },
    computed: {
        nonDismissedAnnouncements() {
            return this.announcements.filter(a => !a.dismissed);
        },

        normalAnnouncements() {
            return this.nonDismissedAnnouncements.filter(a => !a.is_blocking);
        },

        blockingAnnouncements() {
            return this.nonDismissedAnnouncements.filter(a => !!a.is_blocking);
        },

        currentBlockingAnnouncement() {
            return this.blockingAnnouncements.length > 0 ? this.blockingAnnouncements[this.currentIndex] : null;
        },

        visibleAnnouncements() {
            const list = [...this.normalAnnouncements];
            if (this.currentBlockingAnnouncement) {
                list.push(this.currentBlockingAnnouncement);
            }
            return list;
        },

        blockingCounter() {
            const allBlocking = this.blockingAnnouncements;
            if (allBlocking.length <= 1) return null;

            return `${this.currentIndex + 1} / ${allBlocking.length}`;
        }
    },
    methods: {
        prevAnnouncement() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
            }
        },

        nextAnnouncement() {
            if (this.currentIndex < this.blockingAnnouncements.length - 1) {
                this.currentIndex++;
            }
        },
        
        handleClose(id) {
            this.$emit('close', id);
        }
    }
}
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
}
.fade-slide-enter-to,
.fade-slide-leave-from {
    opacity: 1;
    transform: translateY(0) scale(1);
}

/* Backdrop fade */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Modal Internal Scale */
.modal-scale-enter-active {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.modal-scale-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-scale-enter-from {
    opacity: 0;
    transform: scale(0.8) translateY(20px);
}
.modal-scale-enter-to {
    opacity: 1;
    transform: scale(1) translateY(0);
}

.modal-scale-leave-from {
    opacity: 1;
    transform: scale(1);
}
.modal-scale-leave-to {
    opacity: 0;
    transform: scale(1.05);
}
</style>
