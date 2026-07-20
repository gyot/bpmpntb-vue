<template>
    <div class="min-h-screen flex flex-col">
        <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-500" :class="scrolled ? 'shadow-md' : ''" :style="headerStyle" role="banner" aria-label="Header utama">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 lg:h-[72px]">
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <img v-if="setting?.logo" :src="'/upload/settings/' + setting.logo" class="h-9 lg:h-11 drop-shadow-sm" alt="Logo">
                        <div v-else>
                            <span class="text-lg font-bold drop-shadow-sm" :style="{color: scrolled ? 'var(--color-primary)' : '#fff'}">{{ setting?.title || 'BPMP NTB' }}</span>
                        </div>
                    </div>

                    <!-- DESKTOP NAV (unchanged) -->
                    <nav class="hidden lg:flex items-center gap-1" role="navigation" aria-label="Navigasi utama">
                        <router-link to="/" class="nav-link" :class="{'nav-link-active': isHome}">Beranda</router-link>
                        <router-link to="/ppid" class="nav-link" :class="{'nav-link-active': isPpid}">PPID</router-link>
                        <div class="relative group">
                            <a href="#" class="nav-link" @click.prevent>Profil <i class="fas fa-chevron-down text-[9px] ml-1 opacity-40"></i></a>
                            <div class="dropdown">
                                <router-link v-for="p in profil" :key="p.id" :to="`/post/profil/${p.id}/${p.slug}`" class="dropdown-item">{{ p.title }}</router-link>
                            </div>
                        </div>
                        <div class="relative group">
                            <a href="#" class="nav-link" @click.prevent>Layanan <i class="fas fa-chevron-down text-[9px] ml-1 opacity-40"></i></a>
                            <div class="dropdown w-72">
                                <template v-for="svc in layanan" :key="svc.id">
                                    <a v-if="svc.link_type==='external'" :href="svc.link_url" target="_blank" class="dropdown-item flex items-center justify-between">
                                        <span>{{ svc.title }}</span><i class="fas fa-external-link-alt text-[10px] opacity-30"></i>
                                    </a>
                                    <router-link v-else :to="`/layanan/${svc.id}/${svc.slug}`" class="dropdown-item">{{ svc.title }}</router-link>
                                </template>
                                <div v-if="layanan.length===0" class="dropdown-item opacity-50">Belum ada layanan</div>
                            </div>
                        </div>
                        <div class="relative group">
                            <a href="#" class="nav-link" @click.prevent>Rubrik <i class="fas fa-chevron-down text-[9px] ml-1 opacity-40"></i></a>
                            <div class="dropdown">
                                <router-link to="/post/berita" class="dropdown-item">Berita</router-link>
                                <router-link to="/post/artikel" class="dropdown-item">Artikel</router-link>
                                <router-link to="/post/jurnal" class="dropdown-item">Jurnal</router-link>
                                <router-link to="/post/pengumuman" class="dropdown-item">Pengumuman</router-link>
                                <router-link to="/post/unduhan" class="dropdown-item">Unduhan</router-link>
                                <router-link to="/post/buletin" class="dropdown-item">Buletin</router-link>
                            </div>
                        </div>
                        <div class="relative group">
                            <a href="#" class="nav-link" @click.prevent>Galeri <i class="fas fa-chevron-down text-[9px] ml-1 opacity-40"></i></a>
                            <div class="dropdown">
                                <router-link to="/post/galeri" class="dropdown-item">Galeri Foto</router-link>
                                <router-link to="/post/kliping" class="dropdown-item">Galeri Kliping</router-link>
                                <a href="http://www.youtube.com/@bpmp_ntb" target="_blank" class="dropdown-item flex items-center justify-between">
                                    <span>Galeri Video</span><i class="fas fa-external-link-alt text-[10px] opacity-30"></i>
                                </a>
                            </div>
                        </div>
                        <template v-for="(item,idx) in navItems" :key="idx">
                            <router-link v-if="item.link && !item.link.startsWith('http') && !item.children?.length" :to="item.link" class="nav-link" :class="{'nav-link-active': route.path===item.link}">{{ item.title }}</router-link>
                            <a v-else-if="item.link && item.link.startsWith('http') && !item.children?.length" :href="item.link" target="_blank" class="nav-link">{{ item.title }} <i class="fas fa-external-link-alt text-[8px] ml-1 opacity-30"></i></a>
                            <div v-else-if="item.children?.length" class="relative group">
                                <a href="#" class="nav-link" @click.prevent>{{ item.title }} <i class="fas fa-chevron-down text-[9px] ml-1 opacity-40"></i></a>
                                <div class="dropdown">
                                    <template v-for="(child,cidx) in item.children" :key="cidx">
                                        <div v-if="child.children?.length" class="dropdown-item relative group">
                                            <div class="flex items-center justify-between"><span>{{ child.title }}</span><i class="fas fa-chevron-right text-[7px] opacity-30"></i></div>
                                            <div class="dropdown" style="top:-6px;left:100%;margin-left:2px">
                                                <template v-for="(gc,gcidx) in child.children" :key="gcidx">
                                                    <div v-if="gc.children?.length" class="dropdown-item relative group">
                                                        <div class="flex items-center justify-between"><span>{{ gc.title }}</span><i class="fas fa-chevron-right text-[7px] opacity-30"></i></div>
                                                        <div class="dropdown" style="top:-6px;left:100%;margin-left:2px">
                                                            <template v-for="(gc4,gc4idx) in gc.children" :key="gc4idx">
                                                                <router-link v-if="gc4.link && !gc4.link.startsWith('http')" :to="gc4.link" class="dropdown-item">{{ gc4.title }}</router-link>
                                                                <a v-else-if="gc4.link" :href="gc4.link" target="_blank" class="dropdown-item">{{ gc4.title }}</a>
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <router-link v-else-if="gc.link && !gc.link.startsWith('http')" :to="gc.link" class="dropdown-item">{{ gc.title }}</router-link>
                                                    <a v-else-if="gc.link" :href="gc.link" target="_blank" class="dropdown-item">{{ gc.title }}</a>
                                                </template>
                                            </div>
                                        </div>
                                        <router-link v-else-if="child.link && !child.link.startsWith('http')" :to="child.link" class="dropdown-item">{{ child.title }}</router-link>
                                        <a v-else-if="child.link" :href="child.link" target="_blank" class="dropdown-item">{{ child.title }}</a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </nav>

                    <!-- HAMBURGER BUTTON -->
                    <div class="flex items-center gap-3">
                        <button @click="toggleMobile" class="lg:hidden w-11 h-11 flex items-center justify-center rounded-xl transition-all duration-200 hover:bg-white/20" :style="{color: '#fff', textShadow: '0 1px 3px rgba(0,0,0,0.2)'}" aria-label="Buka menu">
                            <span class="block w-5 relative" style="height:14px">
                                <span class="absolute left-0 w-full h-[2px] rounded-full bg-current transition-all duration-300" :style="mobileMenu ? 'top:6px;transform:rotate(45deg)' : 'top:0'"></span>
                                <span class="absolute left-0 w-full h-[2px] rounded-full bg-current transition-all duration-300" :style="mobileMenu ? 'opacity:0' : 'top:6px;opacity:1'"></span>
                                <span class="absolute left-0 w-full h-[2px] rounded-full bg-current transition-all duration-300" :style="mobileMenu ? 'top:6px;transform:rotate(-45deg)' : 'top:12px'"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- MOBILE MENU DRAWER -->
            <transition name="mm-overlay">
                <div v-if="mobileMenu" class="lg:hidden fixed inset-0 z-[60]" @keydown.escape="closeMobile" ref="mobileWrap">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeMobile"></div>
                    <div class="absolute right-0 top-0 h-screen w-[min(85vw,360px)] bg-white shadow-2xl flex flex-col" style="max-height:100vh" @click.stop>
                        <!-- Header -->
                        <div class="flex items-center justify-between px-5 h-16 border-b border-gray-100 flex-shrink-0">
                            <span class="text-base font-bold" style="color:var(--color-primary)">Menu</span>
                            <button @click="closeMobile" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-gray-100 transition-colors"><i class="fas fa-times text-lg text-gray-400"></i></button>
                        </div>
                        <!-- Scrollable menu -->
                        <div class="flex-1 overflow-y-auto py-2" style="min-height:0" role="navigation" aria-label="Menu mobile">
                            <!-- Beranda -->
                            <router-link to="/" @click="closeMobile" class="flex items-center gap-3 px-5 h-12 text-[15px] font-medium text-gray-800 hover:bg-gray-50 no-underline" :class="{'text-blue-600 font-bold bg-blue-50/50': isHome}">
                                <i class="fas fa-home w-5 text-center text-sm opacity-50"></i><span>Beranda</span>
                            </router-link>
                            <!-- PPID -->
                            <router-link to="/ppid" @click="closeMobile" class="flex items-center gap-3 px-5 h-12 text-[15px] font-medium text-gray-800 hover:bg-gray-50 no-underline" :class="{'text-blue-600 font-bold bg-blue-50/50': isPpid}">
                                <i class="fas fa-building w-5 text-center text-sm opacity-50"></i><span>PPID</span>
                            </router-link>

                            <!-- Profil accordion -->
                            <div v-if="profil.length">
                                <button @click="toggleAccordion('profil')" class="flex items-center gap-3 px-5 h-12 text-[15px] font-semibold text-gray-800 hover:bg-gray-50 w-full text-left border-none bg-transparent cursor-pointer">
                                    <i class="fas fa-info-circle w-5 text-center text-sm opacity-50"></i><span class="flex-1">Profil</span>
                                    <i class="fas fa-chevron-down text-[10px] opacity-35 transition-transform duration-250" :style="isOpen('profil') ? 'transform:rotate(180deg)' : ''"></i>
                                </button>
                                <div v-if="isOpen('profil')" class="bg-gray-50 border-y border-gray-100">
                                    <router-link v-for="p in profil" :key="p.id" :to="`/post/profil/${p.id}/${p.slug}`" @click="closeMobile" class="flex items-center gap-3 px-5 pl-13 h-11 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 no-underline relative">
                                        <span class="absolute left-9 w-1 h-1 rounded-full bg-gray-300"></span>{{ p.title }}
                                    </router-link>
                                </div>
                            </div>

                            <!-- Layanan accordion -->
                            <div v-if="layanan.length">
                                <button @click="toggleAccordion('layanan')" class="flex items-center gap-3 px-5 h-12 text-[15px] font-semibold text-gray-800 hover:bg-gray-50 w-full text-left border-none bg-transparent cursor-pointer">
                                    <i class="fas fa-concierge-bell w-5 text-center text-sm opacity-50"></i><span class="flex-1">Layanan</span>
                                    <i class="fas fa-chevron-down text-[10px] opacity-35 transition-transform duration-250" :style="isOpen('layanan') ? 'transform:rotate(180deg)' : ''"></i>
                                </button>
                                <div v-if="isOpen('layanan')" class="bg-gray-50 border-y border-gray-100">
                                    <template v-for="svc in layanan" :key="svc.id">
                                        <a v-if="svc.link_type==='external'" :href="svc.link_url" target="_blank" class="flex items-center gap-3 px-5 pl-13 h-11 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 no-underline relative" @click="closeMobile">
                                            <span class="absolute left-9 w-1 h-1 rounded-full bg-gray-300"></span>{{ svc.title }}<i class="fas fa-external-link-alt text-[9px] opacity-30 ml-1"></i>
                                        </a>
                                        <router-link v-else :to="`/layanan/${svc.id}/${svc.slug}`" @click="closeMobile" class="flex items-center gap-3 px-5 pl-13 h-11 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 no-underline relative">
                                            <span class="absolute left-9 w-1 h-1 rounded-full bg-gray-300"></span>{{ svc.title }}
                                        </router-link>
                                    </template>
                                </div>
                            </div>

                            <!-- Rubrik accordion -->
                            <button @click="toggleAccordion('rubrik')" class="flex items-center gap-3 px-5 h-12 text-[15px] font-semibold text-gray-800 hover:bg-gray-50 w-full text-left border-none bg-transparent cursor-pointer">
                                <i class="fas fa-newspaper w-5 text-center text-sm opacity-50"></i><span class="flex-1">Rubrik</span>
                                <i class="fas fa-chevron-down text-[10px] opacity-35 transition-transform duration-250" :style="isOpen('rubrik') ? 'transform:rotate(180deg)' : ''"></i>
                            </button>
                            <div v-if="isOpen('rubrik')" class="bg-gray-50 border-y border-gray-100">
                                <router-link to="/post/berita" @click="closeMobile" class="flex items-center gap-3 px-5 pl-13 h-11 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 no-underline relative"><span class="absolute left-9 w-1 h-1 rounded-full bg-gray-300"></span>Berita</router-link>
                                <router-link to="/post/artikel" @click="closeMobile" class="flex items-center gap-3 px-5 pl-13 h-11 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 no-underline relative"><span class="absolute left-9 w-1 h-1 rounded-full bg-gray-300"></span>Artikel</router-link>
                                <router-link to="/post/jurnal" @click="closeMobile" class="flex items-center gap-3 px-5 pl-13 h-11 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 no-underline relative"><span class="absolute left-9 w-1 h-1 rounded-full bg-gray-300"></span>Jurnal</router-link>
                                <router-link to="/post/pengumuman" @click="closeMobile" class="flex items-center gap-3 px-5 pl-13 h-11 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 no-underline relative"><span class="absolute left-9 w-1 h-1 rounded-full bg-gray-300"></span>Pengumuman</router-link>
                                <router-link to="/post/unduhan" @click="closeMobile" class="flex items-center gap-3 px-5 pl-13 h-11 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 no-underline relative"><span class="absolute left-9 w-1 h-1 rounded-full bg-gray-300"></span>Unduhan</router-link>
                                <router-link to="/post/buletin" @click="closeMobile" class="flex items-center gap-3 px-5 pl-13 h-11 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 no-underline relative"><span class="absolute left-9 w-1 h-1 rounded-full bg-gray-300"></span>Buletin</router-link>
                            </div>

                            <!-- Galeri accordion -->
                            <button @click="toggleAccordion('galeri')" class="flex items-center gap-3 px-5 h-12 text-[15px] font-semibold text-gray-800 hover:bg-gray-50 w-full text-left border-none bg-transparent cursor-pointer">
                                <i class="fas fa-images w-5 text-center text-sm opacity-50"></i><span class="flex-1">Galeri</span>
                                <i class="fas fa-chevron-down text-[10px] opacity-35 transition-transform duration-250" :style="isOpen('galeri') ? 'transform:rotate(180deg)' : ''"></i>
                            </button>
                            <div v-if="isOpen('galeri')" class="bg-gray-50 border-y border-gray-100">
                                <router-link to="/post/galeri" @click="closeMobile" class="flex items-center gap-3 px-5 pl-13 h-11 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 no-underline relative"><span class="absolute left-9 w-1 h-1 rounded-full bg-gray-300"></span>Galeri Foto</router-link>
                                <router-link to="/post/kliping" @click="closeMobile" class="flex items-center gap-3 px-5 pl-13 h-11 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 no-underline relative"><span class="absolute left-9 w-1 h-1 rounded-full bg-gray-300"></span>Galeri Kliping</router-link>
                                <a href="http://www.youtube.com/@bpmp_ntb" target="_blank" class="flex items-center gap-3 px-5 pl-13 h-11 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 no-underline relative">
                                    <span class="absolute left-9 w-1 h-1 rounded-full bg-gray-300"></span>Galeri Video <i class="fas fa-external-link-alt text-[9px] opacity-30 ml-1"></i>
                                </a>
                            </div>

                            <!-- Custom nav items -->
                            <template v-for="(item,idx) in navItems" :key="'mn'+idx">
                                <!-- Simple link -->
                                <router-link v-if="item.link && !item.link.startsWith('http') && !item.children?.length" :to="item.link" @click="closeMobile" class="flex items-center gap-3 px-5 h-12 text-[15px] font-medium text-gray-800 hover:bg-gray-50 no-underline" :class="{'text-blue-600 font-bold bg-blue-50/50': route.path===item.link}">
                                    <i class="fas fa-link w-5 text-center text-sm opacity-50"></i><span>{{ item.title }}</span>
                                </router-link>
                                <a v-else-if="item.link && item.link.startsWith('http') && !item.children?.length" :href="item.link" target="_blank" class="flex items-center gap-3 px-5 h-12 text-[15px] font-medium text-gray-800 hover:bg-gray-50 no-underline" @click="closeMobile">
                                    <i class="fas fa-external-link-alt w-5 text-center text-sm opacity-50"></i><span>{{ item.title }}</span>
                                </a>
                                <!-- Parent with children -->
                                <MobileAccordion v-else-if="item.children?.length" :item="item" :depth="0" :open-accs="openAccs" :toggle="toggleAccordion" :is-open="isOpen" :close="closeMobile" :route="route" />
                            </template>
                        </div>
                    </div>
                </div>
            </transition>
        </header>

        <div v-if="pengumuman.length && !isHome" class="mt-16 lg:mt-[72px] border-b" style="background:rgba(245,158,11,0.06);border-color:rgba(245,158,11,0.15)">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center gap-3 overflow-hidden">
                <span class="badge-accent flex-shrink-0 text-[10px] uppercase tracking-wider">Pengumuman</span>
                <div class="overflow-hidden flex-1">
                    <p class="whitespace-nowrap text-sm font-medium" style="color:var(--color-text-primary);animation:ticker 25s linear infinite">{{ pengumuman[0]?.title }}</p>
                </div>
            </div>
        </div>

        <main id="main-content" class="flex-1" :class="isHome ? '' : 'mt-16 lg:mt-[72px]'" role="main" aria-label="Konten utama">
            <slot />
        </main>

        <footer role="contentinfo" aria-label="Footer">
            <div style="background:var(--color-secondary)">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                        <div>
                            <div class="mb-5">
                                <img v-if="setting?.logo" :src="'/upload/settings/' + setting.logo" class="h-12" alt="">
                            </div>
                            <p class="text-sm text-white/70 leading-relaxed mb-2 font-semibold">{{ setting?.title || 'BPMP Provinsi NTB' }}</p>
                            <p class="text-sm text-white/50 leading-relaxed whitespace-pre-line mb-5">{{ setting?.alamat }}</p>
                            <div v-if="setting?.map" class="rounded-xl overflow-hidden border border-white/10">
                                <div v-html="setting.map" class="[&_iframe]:w-full [&_iframe]:h-[180px] [&_iframe]:border-0"></div>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-5 pb-3 border-b border-white/10">Rubrik</h4>
                            <ul class="space-y-2.5">
                                <li><router-link to="/post/berita" class="text-sm text-white/60 hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[8px] text-white/30"></i>Berita</router-link></li>
                                <li><router-link to="/post/artikel" class="text-sm text-white/60 hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[8px] text-white/30"></i>Artikel</router-link></li>
                                <li><router-link to="/post/jurnal" class="text-sm text-white/60 hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[8px] text-white/30"></i>Jurnal</router-link></li>
                                <li><router-link to="/post/pengumuman" class="text-sm text-white/60 hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[8px] text-white/30"></i>Pengumuman</router-link></li>
                                <li><router-link to="/post/unduhan" class="text-sm text-white/60 hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[8px] text-white/30"></i>Unduhan</router-link></li>
                                <li><router-link to="/post/buletin" class="text-sm text-white/60 hover:text-white transition flex items-center gap-2"><i class="fas fa-chevron-right text-[8px] text-white/30"></i>Buletin</router-link></li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-5 pb-3 border-b border-white/10">Visitor</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-white/50">Total Pengunjung</span>
                                    <span class="text-white font-bold bg-white/10 px-3 py-1 rounded-full text-xs">{{ visitorStats?.totalVisitors||0 }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-white/50">Pengunjung Hari Ini</span>
                                    <span class="text-white font-bold bg-white/10 px-3 py-1 rounded-full text-xs">{{ visitorStats?.todayVisitors||0 }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-white/50">Pengunjung Bulan Ini</span>
                                    <span class="text-white font-bold bg-white/10 px-3 py-1 rounded-full text-xs">{{ visitorStats?.thismonthVisitors||0 }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-white/50">Pengunjung Online</span>
                                    <span class="font-bold px-3 py-1 rounded-full text-xs" style="background:rgba(245,158,11,0.2);color:var(--color-accent)">{{ visitorStats?.onlineVisitors||0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-5 pb-3 border-b border-white/10">Hubungi Kami</h4>
                            <ul class="space-y-3.5 text-sm text-white/60">
                                <li class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-phone-alt text-[10px] text-white/50"></i></div>
                                    <span>{{ setting?.phone || '-' }}</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-envelope text-[10px] text-white/50"></i></div>
                                    <span>{{ setting?.email || '-' }}</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-map-marker-alt text-[10px] text-white/50"></i></div>
                                    <span>{{ setting?.alamat || '-' }}</span>
                                </li>
                            </ul>
                            <div class="flex gap-2 mt-5">
                                <a v-if="setting?.facebook" :href="setting.facebook" target="_blank" class="w-9 h-9 rounded-full flex items-center justify-center bg-white/10 hover:bg-white/20 text-white/60 hover:text-white transition"><i class="fab fa-facebook-f text-xs"></i></a>
                                <a v-if="setting?.twitter" :href="setting.twitter" target="_blank" class="w-9 h-9 rounded-full flex items-center justify-center bg-white/10 hover:bg-white/20 text-white/60 hover:text-white transition"><i class="fab fa-twitter text-xs"></i></a>
                                <a v-if="setting?.instagram" :href="setting.instagram" target="_blank" class="w-9 h-9 rounded-full flex items-center justify-center bg-white/10 hover:bg-white/20 text-white/60 hover:text-white transition"><i class="fab fa-instagram text-xs"></i></a>
                                <a v-if="setting?.youtube" :href="setting.youtube" target="_blank" class="w-9 h-9 rounded-full flex items-center justify-center bg-white/10 hover:bg-white/20 text-white/60 hover:text-white transition"><i class="fab fa-youtube text-xs"></i></a>
                                <a v-if="setting?.whatsapp" :href="'https://wa.me/'+setting.whatsapp.replace(/[^0-9]/g,'')" target="_blank" class="w-9 h-9 rounded-full flex items-center justify-center bg-white/10 hover:bg-white/20 text-white/60 hover:text-white transition"><i class="fab fa-whatsapp text-xs"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="background:color-mix(in srgb, var(--color-secondary) 85%, black)">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2">
                    <p class="text-xs text-white/30">&copy; {{ new Date().getFullYear() }} {{ setting?.title || 'BPMP Provinsi NTB' }}. Kementerian Pendidikan Dasar dan Menengah.</p>
                    <p class="text-xs text-white/20">Republik Indonesia</p>
                </div>
            </div>
        </footer>
        <Chatbot />
        <AccessibilityWidget />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/bootstrap.js';
import Chatbot from '@/components/Chatbot.vue';
import AccessibilityWidget from '@/components/AccessibilityWidget.vue';
import MobileAccordion from '@/components/MobileAccordion.vue';

const route = useRoute();
const router = useRouter();
const setting = ref(null);
const profil = ref([]);
const layanan = ref([]);
const pengumuman = ref([]);
const visitorStats = ref(null);
const scrolled = ref(false);
const mobileMenu = ref(false);
const openAccs = ref(new Set());
const mobileWrap = ref(null);

const isHome = computed(() => route.path === '/');
const isPpid = computed(() => route.path.startsWith('/ppid'));
const navItems = computed(() => {
    try {
        const raw = JSON.parse(setting.value?.navigations || '[]');
        if (!Array.isArray(raw)) return [];
        const root = [];
        const stack = [{ level: -1, children: root }];
        for (const item of raw) {
            const node = { ...item, children: [] };
            while (stack.length > 1 && stack[stack.length - 1].level >= node.level) stack.pop();
            stack[stack.length - 1].children.push(node);
            stack.push(node);
        }
        return root;
    } catch { return []; }
});

// Mobile menu
function toggleMobile() {
    mobileMenu.value = !mobileMenu.value;
    if (mobileMenu.value) {
        document.body.style.overflow = 'hidden';
        openAccs.value = new Set();
        autoOpenActive();
    } else {
        document.body.style.overflow = '';
    }
}
function closeMobile() {
    mobileMenu.value = false;
    document.body.style.overflow = '';
}
function toggleAccordion(key) {
    const s = new Set(openAccs.value);
    s.has(key) ? s.delete(key) : s.add(key);
    openAccs.value = s;
}
function isOpen(key) { return openAccs.value.has(key); }

// Auto-open parent if current route is a child
function autoOpenActive() {
    const path = route.path;
    if (profil.value.some(p => `/post/profil/${p.id}/${p.slug}` === path)) openAccs.value.add('profil');
    if (layanan.value.some(s => `/layanan/${s.id}/${s.slug}` === path)) openAccs.value.add('layanan');
    if (['/post/berita','/post/artikel','/post/jurnal','/post/pengumuman','/post/unduhan','/post/buletin'].includes(path)) openAccs.value.add('rubrik');
    if (['/post/galeri','/post/kliping'].includes(path)) openAccs.value.add('galeri');
    // Custom nav items
    const check = (items) => {
        for (const item of items) {
            if (item.link === path) return true;
            if (item.children?.length && check(item.children)) {
                openAccs.value.add('nav_' + item.title);
                return true;
            }
        }
        return false;
    };
    check(navItems.value);
}

// Close on route change
watch(() => route.path, () => closeMobile());

// Escape key
function onKeydown(e) { if (e.key === 'Escape' && mobileMenu.value) closeMobile(); }

function hexToRgba(hex, alpha) {
    if (!hex) return `rgba(37,99,235,${alpha})`;
    hex = hex.replace('#', '');
    if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
    const r = parseInt(hex.substring(0,2), 16);
    const g = parseInt(hex.substring(2,4), 16);
    const b = parseInt(hex.substring(4,6), 16);
    return `rgba(${r},${g},${b},${alpha})`;
}

const headerStyle = computed(() => {
    const primary = getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim() || '#2563eb';
    if (scrolled.value) {
        return { background: primary, backdropFilter: 'blur(16px)', borderBottom: '1px solid rgba(255,255,255,0.1)' };
    }
    return { background: hexToRgba(primary, 0.55), backdropFilter: 'blur(16px)', borderBottom: '1px solid rgba(255,255,255,0.1)' };
});

function onScroll() { scrolled.value = window.scrollY > 60; }

function setupDropdownFlip() {
    const header = document.querySelector('header');
    if (!header) return;
    header.addEventListener('mouseenter', (e) => {
        const group = e.target.closest('.dropdown .group');
        if (!group) return;
        const sub = group.querySelector(':scope > .dropdown');
        if (!sub) return;
        sub.classList.remove('dd-flip');
        const rect = sub.getBoundingClientRect();
        if (rect.right > window.innerWidth - 8) sub.classList.add('dd-flip');
    }, true);
}

onMounted(async () => {
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('keydown', onKeydown);
    onScroll();
    setupDropdownFlip();
    const results = await Promise.allSettled([api.get('/settings'), api.get('/beranda'), api.get('/visitor-stats'), api.get('/layanans-public')]);
    const labels = ['settings','beranda','visitor-stats','layanans'];
    results.forEach((r, i) => {
        if (r.status==='fulfilled') console.log('[API OK]', labels[i], r.value.data);
        else console.warn('[API FAIL]', labels[i], r.reason?.response?.status, r.reason?.message);
    });
    const [s, b, v, l] = results;
    if (s.status==='fulfilled') setting.value = s.value.data;
    if (b.status==='fulfilled') { profil.value = b.value.data.profil||[]; pengumuman.value = b.value.data.pengumuman||[]; }
    if (v.status==='fulfilled') visitorStats.value = v.value.data;
    if (l.status==='fulfilled') layanan.value = l.value.data||[];
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});
</script>

<style scoped>
.nav-link {
    display: inline-flex; align-items: center; padding: 6px 14px;
    font-size: 14px; font-weight: 600; border-radius: 10px;
    color: rgba(255,255,255,0.85); transition: all 0.2s;
    text-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.nav-link:hover { background: rgba(255,255,255,0.15); color: #fff; }
.nav-link-active { color: #fff; background: rgba(255,255,255,0.2); }

header:not([style*="transparent"]) .nav-link { color: rgba(255,255,255,0.85); text-shadow: 0 1px 3px rgba(0,0,0,0.1); }
header:not([style*="transparent"]) .nav-link:hover { background: rgba(255,255,255,0.15); color: #fff; }
header:not([style*="transparent"]) .nav-link-active { color: #fff; background: rgba(255,255,255,0.2); }

.group:hover > .dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
.dropdown .group:hover > .dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
.dd-flip { left: auto !important; right: 100% !important; margin-left: 0 !important; margin-right: 4px !important; }
.dropdown {
    position: absolute; left: 0; top: calc(100% + 8px); min-width: 220px;
    background: white; border-radius: 16px; padding: 6px;
    box-shadow: 0 12px 48px rgba(0,0,0,0.12); border: 1px solid #f3f4f6;
    opacity: 0; visibility: hidden; transform: translateY(8px);
    transition: all 0.2s ease; z-index: 50;
}
.dropdown-item {
    display: block; padding: 9px 14px; border-radius: 10px;
    font-size: 13px; font-weight: 500; color: var(--color-text-primary);
    transition: all 0.15s;
}
.dropdown-item:hover { background: #f3f4f6; color: var(--color-primary); }

@keyframes ticker { 0%{transform:translateX(100%)} 100%{transform:translateX(-100%)} }
</style>

<style>
/* Mobile menu - NOT scoped so it applies to MobileAccordion.vue child component */
.mm-item {
    display: flex; align-items: center; gap: 12px;
    padding: 0 20px; height: 48px; min-height: 48px;
    font-size: 15px; font-weight: 500; color: #1f2937;
    transition: background .15s, color .15s;
    text-decoration: none; cursor: pointer; border: none; background: none; width: 100%; text-align: left;
}
.mm-item:hover, .mm-item:focus { background: #f9fafb; }
.mm-active { color: var(--color-primary); font-weight: 700; background: rgba(37,99,235,0.04); }
.mm-icon { width: 20px; text-align: center; font-size: 14px; opacity: 0.5; flex-shrink: 0; }
.mm-parent { font-weight: 600; }
.mm-child { padding-left: 52px; height: 44px; min-height: 44px; font-size: 14px; color: #4b5563; position: relative; }
.mm-child::before { content: ''; position: absolute; left: 36px; top: 50%; width: 4px; height: 4px; border-radius: 50%; background: #d1d5db; transform: translateY(-50%); }
.mm-child:hover { color: var(--color-primary); }
.mm-child-active { color: var(--color-primary); font-weight: 600; background: rgba(37,99,235,0.04); }
.mm-child-active::before { background: var(--color-primary); }
.mm-sub { background: #f9fafb; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; }
.mm-sub .mm-sub { background: #f3f4f6; }
.mm-sub .mm-child { padding-left: 72px; }
.mm-sub .mm-child::before { left: 56px; }
.mm-sub .mm-sub .mm-child { padding-left: 92px; }
.mm-sub .mm-sub .mm-child::before { left: 76px; }
.mm-chevron { font-size: 10px; opacity: 0.35; transition: transform .25s ease; flex-shrink: 0; }
.mm-chevron-open { transform: rotate(180deg); }
/* Transitions */
.mm-overlay-enter-active { transition: opacity .25s ease; }
.mm-overlay-leave-active { transition: opacity .2s ease; }
.mm-overlay-enter-from, .mm-overlay-leave-to { opacity: 0; }
.mm-expand-enter-active { transition: all .25s ease; overflow: hidden; }
.mm-expand-leave-active { transition: all .2s ease; overflow: hidden; }
.mm-expand-enter-from, .mm-expand-leave-to { opacity: 0; max-height: 0; }
.mm-expand-enter-to, .mm-expand-leave-from { max-height: 600px; }
</style>
