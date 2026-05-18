<template>
    <div>
        <div class="fixed inset-0 bg-white/70 z-40"></div>
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="spinner">
                <div v-for="n in 12" :key="n" :class="'bar bar' + n"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "LoadingSpinner",
}
</script>

<style scoped>
.spinner{
    position: relative;
    width:40px;
    height:40px;
}

.bar{
    position:absolute;
    top:3px;
    left:50%;
    width:3px;
    height:9px;
    background:var(--primary);
    border-radius:3px;
    transform-origin:center 17px;
    animation: fade 1.2s linear infinite;
}

/* ROTATION POSITIONS */
.bar1  { transform: rotate(0deg);   animation-delay:-1.1s }
.bar2  { transform: rotate(30deg);  animation-delay:-1s }
.bar3  { transform: rotate(60deg);  animation-delay:-0.9s }
.bar4  { transform: rotate(90deg);  animation-delay:-0.8s }
.bar5  { transform: rotate(120deg); animation-delay:-0.7s }
.bar6  { transform: rotate(150deg); animation-delay:-0.6s }
.bar7  { transform: rotate(180deg); animation-delay:-0.5s }
.bar8  { transform: rotate(210deg); animation-delay:-0.4s }
.bar9  { transform: rotate(240deg); animation-delay:-0.3s }
.bar10 { transform: rotate(270deg); animation-delay:-0.2s }
.bar11 { transform: rotate(300deg); animation-delay:-0.1s }
.bar12 { transform: rotate(330deg); animation-delay:0s }

@keyframes fade{
    0%   { opacity:1 }
    100% { opacity:0.1 }
}

</style>
