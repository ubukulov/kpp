<script>
export default {
    props: [
        'systemText'
    ],
    methods: {
        closeInformation() {
            this.$emit('close-information');
        }
    }
}
</script>

<template>
    <transition name="fade">
        <div class="system-info">
            <v-icon color="green">mdi-checkbox-marked-circle-outline</v-icon>
            <span v-html="systemText"></span>
            <v-icon @click="closeInformation()">mdi-close</v-icon>
        </div>
    </transition>
</template>

<style scoped>
.system-info {
    display: flex;
    gap: 20px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    width: 350px;
    position: absolute;
    right: 10px;
    padding: 10px 20px;
    justify-content: space-between;
}
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
