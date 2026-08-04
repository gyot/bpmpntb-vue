<template>
    <div class="dropdown-item group relative" style="cursor:pointer">
        <div class="flex items-center justify-between gap-2">
            <component v-if="!hasChildren" :is="tag" v-bind="linkProps" class="block -mx-[9px] -my-[6px] px-[9px] py-[6px] rounded-[10px] hover:bg-gray-100">{{ item.title }}</component>
            <span v-else>{{ item.title }}</span>
            <i v-if="hasChildren" class="fas fa-chevron-right text-[7px] opacity-30"></i>
        </div>
        <div v-if="hasChildren" class="dropdown" style="top:-6px;left:100%;margin-left:2px;min-width:200px">
            <template v-for="(child,i) in item.children" :key="i">
                <NavNestedItem v-if="child.children?.length" :item="child" />
                <router-link v-else-if="child.link && !child.link.startsWith('http')" :to="child.link" class="dropdown-item">{{ child.title }}</router-link>
                <a v-else-if="child.link" :href="child.link" target="_blank" class="dropdown-item">{{ child.title }}</a>
            </template>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps({ item: { type: Object, required: true } });
const hasChildren = computed(() => props.item.children?.length > 0);
const isExternal = computed(() => props.item.link && props.item.link.startsWith('http'));
const tag = computed(() => isExternal.value ? 'a' : 'router-link');
const linkProps = computed(() => {
    if (isExternal.value) return { href: props.item.link, target: '_blank', rel: 'noopener' };
    if (props.item.link) return { to: props.item.link };
    return {};
});
</script>
