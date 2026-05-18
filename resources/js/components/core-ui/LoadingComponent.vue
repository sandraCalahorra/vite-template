<template>
  <div>
    <!-- SPINNER -->
    <div class="flex item-center justify-center z-40" :class="customClass">
        <div class="flex justify-center">
            <template v-if="isComponent">
                <component :is="image" id="html-spinner"
                    :style="{ width: width + 'px', height: height + 'px', color: color }"/>
            </template>
            <template v-else>
                <img :src="image" id="html-spinner" :width="width" :height="height">
            </template>
        </div>
    </div>

  </div>
</template>

<script>

export default {
  name: "LoadingComponent",
  props: {
    // Route for the image or the component name
    image: {
        type: String,
        required: true
    },
    // this prop triggers the component to render a component instead of an image
    isComponent: {
        type: Boolean,
    },
    width: {
        type: String,
        default: "70"
    },
    height: {
        type: String,
        default: "70"
    },
    customClass: {
        type: String,
        default: ""
    },
    color: {
        type: String,
        default: '#004ea3'
    }
  }
}
</script>

<style scoped>
    #html-spinner{

        /*Animation code for older Chrome, Safari, Opera*/
        -webkit-transition-property: -webkit-transform;
        -webkit-transition-duration: 2s;
        -webkit-animation-name: rotation;
        -webkit-animation-iteration-count: infinite;
        -webkit-animation-timing-function: ease-in-out;

        /*Animation code for older Firefox*/
        -moz-transition-property: -moz-transform;
        -moz-animation-name: rotation;
        -moz-animation-duration: 2s;
        -moz-animation-iteration-count: infinite;
        -moz-animation-timing-function: ease-in-out;

        /*Default animation code*/
        transition-property: transform;
        animation-name: rotation; /*Call to @keyframe rule*/
        animation-duration: 2s; /*Change to speed up or slow down the animation*/
        animation-iteration-count: infinite; /*Make the animation go forever*/
        animation-timing-function: ease-in-out;
    }

    @keyframes rotation {
        0% {transform: rotate(0deg);}
        20% {transform: rotate(90deg);}
        40% {transform: rotate(180deg);}
        60% {transform: rotate(270deg);}
        80% {transform: rotate(360deg);}
        100% {transform: rotate(360deg);}
    }

</style>
