<template>
    <div>
        <!-- Has children: accordion -->
        <div v-if="item.children?.length">
            <button @click="toggle(key)" class="mm-item mm-parent" :class="depthClass" :aria-expanded="isOpen(key)">
                <i class="fas fa-link mm-icon"></i>
                <span class="flex-1">{{ item.title }}</span>
                <i class="fas fa-chevron-down mm-chevron" :class="{'mm-chevron-open': isOpen(key)}"></i>
            </button>
            <transition name="mm-expand">
                <div v-if="isOpen(key)" class="mm-sub">
                    <template v-for="(child,i) in item.children" :key="i">
                        <!-- Child with sub-children: recursive -->
                        <MobileAccordion v-if="child.children?.length" :item="child" :depth="depth+1" :open-accs="openAccs" :toggle="toggle" :is-open="isOpen" :close="close" :route="route" />
                        <!-- Leaf child: link -->
                        <router-link v-else-if="child.link && !child.link.startsWith('http')" :to="child.link" @click="close" class="mm-item mm-child" :class="{'mm-child-active': route.path===child.link}">{{ child.title }}</router-link>
                        <a v-else-if="child.link" :href="child.link" target="_blank" class="mm-item mm-child" @click="close">{{ child.title }}</a>
                    </template>
                </div>
            </transition>
        </div>
        <!-- No children: simple link -->
        <router-link v-else-if="item.link && !item.link.startsWith('http')" :to="item.link" @click="close" class="mm-item" :class="{'mm-active': route.path===item.link}">
            <i class="fas fa-link mm-icon"></i><span>{{ item.title }}</span>
        </router-link>
        <a v-else-if="item.link" :href="item.link" target="_blank" class="mm-item" @click="close">
            <i class="fas fa-external-link-alt mm-icon"></i><span>{{ item.title }}</span>
        </a>
    </div>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps({
    item: { type: Object, required: true },
    depth: { type: Number, default: 0 },
    openAccs: { type: Object, required: true },
    toggle: { type: Function, required: true },
    isOpen: { type: Function, required: true },
    close: { type: Function, required: true },
    route: { type: Object, required: true },
});
const key = computed(() => 'nav_' + props.item.title + '_' + props.depth);
const depthClass = computed(() => props.depth > 0 ? 'mm-child' : '');
</script>
