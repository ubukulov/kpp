<script>
export default {
    name: "AlertSuccess",
    props: ['message']
}
</script>

<template>
    <v-alert
        dense
        text
        type="success"
    >
        {{ message }}
    </v-alert>
</template>

<style scoped>

</style>
