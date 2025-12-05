<script>
export default {
    name: "Loading",
    props: [
        'show'
    ]
}
</script>

<template>

    <div v-if="show" class="loading-overlay">
        <v-progress-circular
            :size="70"
            :width="7"
            color="green"
            indeterminate
        ></v-progress-circular>
    </div>

</template>

<style scoped>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.75);
    z-index: 999999; /* поверх диалогов */
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}
</style>
