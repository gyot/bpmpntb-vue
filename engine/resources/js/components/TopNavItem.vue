<template>
    <div class="relative group">
        <a href="#" class="nav-link" @click.prevent>{{ item.title }} <i class="fas fa-chevron-down text-[9px] ml-1 opacity-40"></i></a>
        <div class="dropdown">
            <template v-for="(child,i) in item.children" :key="i">
                <NavNestedItem v-if="child.children?.length" :item="child" />
                <router-link v-else-if="child.link && !child.link.startsWith('http')" :to="child.link" class="dropdown-item">{{ child.title }}</router-link>
                <a v-else-if="child.link" :href="child.link" target="_blank" class="dropdown-item">{{ child.title }}</a>
            </template>
        </div>
    </div>
</template>
<script setup>
import NavNestedItem from './NavNestedItem.vue';
defineProps({ item: { type: Object, required: true } });
</script>
