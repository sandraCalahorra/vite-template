<template>
    <div class="flex flex-row gap-2" :title="renderIconTooltip()">
        <IconPirineos v-if="icons.includes('pirineos')" class="size-6" />
        <IconVidSignerSolid v-if="icons.includes('vidsignerSigned')" class="size-6" />
        <IconVidSigner v-else-if="icons.includes('vidsigner')" class="size-6" />
        <HandRaisedSolid v-if="icons.includes('on_hold')" class="size-6 text-yellow-500" />
        <IconBan v-if="icons.includes('disabled')" class="size-6 text-red-700" />
        <IconWarningFilled v-if="icons.includes('warning')" class="size-6 text-orange-500" />
    </div>
</template>

<script>
export default {
    name: 'IconsCell',
    props: {
        icons: Array,
        row: Object,
    },
    methods: {
        renderIconTooltip() {
            if (this.icons.includes('pirineos')) {
                return 'Pirineos';
            }
            if (this.icons.includes('vidsignerSigned')) {
                return 'Enviado a VidSigner y Firmado';
            }
            if (this.icons.includes('vidsigner')) {
                return 'Enviado a VidSigner';
            }
            if (this.icons.includes('on_hold')) {
                return 'Proyecto marcado como detenido';
            }
            if (this.icons.includes('disabled')) {
                return 'Proyecto deshabilitado';
            }
            if (this.icons.includes('warning')) {
                return 'Proyecto con incidencias';
            }
            return '';
        },
    },
};
</script>
