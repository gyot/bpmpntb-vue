<template>
    <div class="min-h-screen flex flex-col">
        <a href="#main-content" class="skip-link">Langsung ke konten utama</a>

        <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" :style="headerStyle" role="banner" aria-label="Header utama">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 lg:h-[72px]">
                    <router-link to="/" class="flex items-center gap-3 flex-shrink-0 group">
                        <img v-if="setting?.logo" :src="'/upload/settings/' + setting.logo" class="h-12 lg:h-14 w-auto object-contain transition-transform duration-300 group-hover:scale-105" :alt="setting?.title || 'Logo'" style="image-rendering:-webkit-optimize-contrast" width="56" height="56">
                        <div v-else class="flex flex-col">
                            <span class="text-lg lg:text-xl font-extrabold tracking-tight" style="color:#fff">BPMP NTB</span>
                            <span class="text-[10px] font-medium tracking-wider uppercase" style="color:rgba(255,255,255,0.7)">Provinsi NTB</span>
                        </div>
                    </router-link>

                    <nav class="hidden lg:flex items-center gap-1" role="navigation" aria-label="Navigasi utama">
                        <router-link to="/" class="nav-link" :class="{'nav-link-active': isHome}">Beranda</router-link>
                        <router-link to="/ppid" class="nav-link" :class="{'nav-link-active': isPpid}">PPID</router-link>
                        <div class="relative group">
                            <a href="#" class="nav-link" @click.prevent>Profil <i class="fas fa-chevron-down text-[9px] ml-1 opacity-40 transition-transform duration-200 group-hover:rotate-180"></i></a>
                            <div class="dropdown">
                                <router-link v-for="p in profil" :key="p.id" :to="`/post/profil/${p.id}/${p.slug}`" class="dropdown-item">{{ p.title }}</router-link>
                            </div>
                        </div>
                        <div class="relative group">
                            <a href="#" class="nav-link" @click.prevent>Layanan <i class="fas fa-chevron-down text-[9px] ml-1 opacity-40 transition-transform duration-200 group-hover:rotate-180"></i></a>
                            <div class="dropdown w-72">
                                <template v-for="svc in layanan" :key="svc.id">
                                    <a v-if="svc.link_type==='external'" :href="svc.link_url" target="_blank" rel="noopener noreferrer" class="dropdown-item flex items-center justify-between">
                                        <span>{{ svc.title }}</span><i class="fas fa-external-link-alt text-[10px] opacity-30"></i>
                                    </a>
                                    <router-link v-else :to="`/layanan/${svc.id}/${svc.slug}`" class="dropdown-item">{{ svc.title }}</router-link>
                                </template>
                                <div v-if="layanan.length===0" class="dropdown-item opacity-50">Belum ada layanan</div>
                            </div>
                        </div>
                        <div class="relative group">
                            <a href="#" class="nav-link" @click.prevent>Rubrik <i class="fas fa-chevron-down text-[9px] ml-1 opacity-40 transition-transform duration-200 group-hover:rotate-180"></i></a>
                            <div class="dropdown">
                                <router-link to="/post/berita" class="dropdown-item"><i class="fas fa-newspaper w-5 text-center text-[11px] opacity-40 mr-2"></i>Berita</router-link>
                                <router-link to="/post/artikel" class="dropdown-item"><i class="fas fa-file-alt w-5 text-center text-[11px] opacity-40 mr-2"></i>Artikel</router-link>
                                <router-link to="/post/jurnal" class="dropdown-item"><i class="fas fa-book w-5 text-center text-[11px] opacity-40 mr-2"></i>Jurnal</router-link>
                                <router-link to="/post/pengumuman" class="dropdown-item"><i class="fas fa-bullhorn w-5 text-center text-[11px] opacity-40 mr-2"></i>Pengumuman</router-link>
                                <router-link to="/post/unduhan" class="dropdown-item"><i class="fas fa-download w-5 text-center text-[11px] opacity-40 mr-2"></i>Unduhan</router-link>
                                <router-link to="/post/buletin" class="dropdown-item"><i class="fas fa-envelope-open w-5 text-center text-[11px] opacity-40 mr-2"></i>Buletin</router-link>
                            </div>
                        </div>
                        <div class="relative group">
                            <a href="#" class="nav-link" @click.prevent>Galeri <i class="fas fa-chevron-down text-[9px] ml-1 opacity-40 transition-transform duration-200 group-hover:rotate-180"></i></a>
                            <div class="dropdown">
                                <router-link to="/post/galeri" class="dropdown-item"><i class="fas fa-images w-5 text-center text-[11px] opacity-40 mr-2"></i>Galeri Foto</router-link>
                                <router-link to="/post/kliping" class="dropdown-item"><i class="fas fa-clone w-5 text-center text-[11px] opacity-40 mr-2"></i>Galeri Kliping</router-link>
                                <a href="http://www.youtube.com/@bpmp_ntb" target="_blank" rel="noopener noreferrer" class="dropdown-item flex items-center justify-between">
                                    <span><i class="fab fa-youtube w-5 text-center text-[11px] opacity-40 mr-2"></i>Galeri Video</span><i class="fas fa-external-link-alt text-[10px] opacity-30"></i>
                                </a>
                            </div>
                        </div>
                        <template v-for="(item,idx) in navItems" :key="idx">
                            <router-link v-if="item.link && !item.link.startsWith('http') && !item.children?.length" :to="item.link" class="nav-link" :class="{'nav-link-active': route.path===item.link}">{{ item.title }}</router-link>
                            <a v-else-if="item.link && item.link.startsWith('http') && !item.children?.length" :href="item.link" target="_blank" rel="noopener noreferrer" class="nav-link">{{ item.title }} <i class="fas fa-external-link-alt text-[8px] ml-1 opacity-30"></i></a>
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
                                                                <a v-else-if="gc4.link" :href="gc4.link" target="_blank" rel="noopener noreferrer" class="dropdown-item">{{ gc4.title }}</a>
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <router-link v-else-if="gc.link && !gc.link.startsWith('http')" :to="gc.link" class="dropdown-item">{{ gc.title }}</router-link>
                                                    <a v-else-if="gc.link" :href="gc.link" target="_blank" rel="noopener noreferrer" class="dropdown-item">{{ gc.title }}</a>
                                                </template>
                                            </div>
                                        </div>
                                        <router-link v-else-if="child.link && !child.link.startsWith('http')" :to="child.link" class="dropdown-item">{{ child.title }}</router-link>
                                        <a v-else-if="child.link" :href="child.link" target="_blank" rel="noopener noreferrer" class="dropdown-item">{{ child.title }}</a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </nav>

                    <div class="flex items-center gap-3">
                        <button @click="toggleMobile" class="lg:hidden w-11 h-11 flex items-center justify-center rounded-xl transition-all duration-200" style="color:#fff" aria-label="Buka menu">
                            <span class="block w-5 relative" style="height:14px">
                                <span class="absolute left-0 w-full h-[2px] rounded-full bg-current transition-all duration-300" :style="mobileMenu ? 'top:6px;transform:rotate(45deg)' : 'top:0'"></span>
                                <span class="absolute left-0 w-full h-[2px] rounded-full bg-current transition-all duration-300" :style="mobileMenu ? 'opacity:0' : 'top:6px;opacity:1'"></span>
                                <span class="absolute left-0 w-full h-[2px] rounded-full bg-current transition-all duration-300" :style="mobileMenu ? 'top:6px;transform:rotate(-45deg)' : 'top:12px'"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <transition name="mm-overlay">
                <div v-if="mobileMenu" class="lg:hidden fixed inset-0 z-[60]" @keydown.escape="closeMobile" ref="mobileWrap">
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeMobile"></div>
                    <div class="absolute right-0 top-0 h-screen w-[min(85vw,360px)] bg-white shadow-2xl flex flex-col" style="max-height:100vh" @click.stop>
                        <div class="flex items-center justify-between px-6 h-16 border-b border-gray-100 flex-shrink-0">
                            <span class="text-base font-bold" style="color:var(--color-primary)">Menu</span>
                            <button @click="closeMobile" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-gray-100 transition-colors"><i class="fas fa-times text-lg text-gray-400"></i></button>
                        </div>
                        <div class="flex-1 overflow-y-auto py-2" style="min-height:0" role="navigation" aria-label="Menu mobile">
                            <router-link to="/" @click="closeMobile" class="mm-item" :class="{'mm-active': isHome}">
                                <i class="fas fa-home mm-icon"></i><span>Beranda</span>
                            </router-link>
                            <router-link to="/ppid" @click="closeMobile" class="mm-item" :class="{'mm-active': isPpid}">
                                <i class="fas fa-building mm-icon"></i><span>PPID</span>
                            </router-link>
                            <div v-if="profil.length">
                                <button @click="toggleAccordion('profil')" class="mm-item mm-parent">
                                    <i class="fas fa-info-circle mm-icon"></i><span class="flex-1">Profil</span>
                                    <i class="fas fa-chevron-down mm-chevron" :class="{'mm-chevron-open': isOpen('profil')}"></i>
                                </button>
                                <div v-if="isOpen('profil')" class="mm-sub">
                                    <router-link v-for="p in profil" :key="p.id" :to="`/post/profil/${p.id}/${p.slug}`" @click="closeMobile" class="mm-item mm-child">{{ p.title }}</router-link>
                                </div>
                            </div>
                            <div v-if="layanan.length">
                                <button @click="toggleAccordion('layanan')" class="mm-item mm-parent">
                                    <i class="fas fa-concierge-bell mm-icon"></i><span class="flex-1">Layanan</span>
                                    <i class="fas fa-chevron-down mm-chevron" :class="{'mm-chevron-open': isOpen('layanan')}"></i>
                                </button>
                                <div v-if="isOpen('layanan')" class="mm-sub">
                                    <template v-for="svc in layanan" :key="svc.id">
                                        <a v-if="svc.link_type==='external'" :href="svc.link_url" target="_blank" rel="noopener noreferrer" class="mm-item mm-child" @click="closeMobile">
                                            {{ svc.title }}<i class="fas fa-external-link-alt text-[9px] opacity-30 ml-1"></i>
                                        </a>
                                        <router-link v-else :to="`/layanan/${svc.id}/${svc.slug}`" @click="closeMobile" class="mm-item mm-child">{{ svc.title }}</router-link>
                                    </template>
                                </div>
                            </div>
                            <button @click="toggleAccordion('rubrik')" class="mm-item mm-parent">
                                <i class="fas fa-newspaper mm-icon"></i><span class="flex-1">Rubrik</span>
                                <i class="fas fa-chevron-down mm-chevron" :class="{'mm-chevron-open': isOpen('rubrik')}"></i>
                            </button>
                            <div v-if="isOpen('rubrik')" class="mm-sub">
                                <router-link to="/post/berita" @click="closeMobile" class="mm-item mm-child">Berita</router-link>
                                <router-link to="/post/artikel" @click="closeMobile" class="mm-item mm-child">Artikel</router-link>
                                <router-link to="/post/jurnal" @click="closeMobile" class="mm-item mm-child">Jurnal</router-link>
                                <router-link to="/post/pengumuman" @click="closeMobile" class="mm-item mm-child">Pengumuman</router-link>
                                <router-link to="/post/unduhan" @click="closeMobile" class="mm-item mm-child">Unduhan</router-link>
                                <router-link to="/post/buletin" @click="closeMobile" class="mm-item mm-child">Buletin</router-link>
                            </div>
                            <button @click="toggleAccordion('galeri')" class="mm-item mm-parent">
                                <i class="fas fa-images mm-icon"></i><span class="flex-1">Galeri</span>
                                <i class="fas fa-chevron-down mm-chevron" :class="{'mm-chevron-open': isOpen('galeri')}"></i>
                            </button>
                            <div v-if="isOpen('galeri')" class="mm-sub">
                                <router-link to="/post/galeri" @click="closeMobile" class="mm-item mm-child">Galeri Foto</router-link>
                                <router-link to="/post/kliping" @click="closeMobile" class="mm-item mm-child">Galeri Kliping</router-link>
                                <a href="http://www.youtube.com/@bpmp_ntb" target="_blank" rel="noopener noreferrer" class="mm-item mm-child" @click="closeMobile">Galeri Video <i class="fas fa-external-link-alt text-[9px] opacity-30 ml-1"></i></a>
                            </div>
                            <template v-for="(item,idx) in navItems" :key="'mn'+idx">
                                <router-link v-if="item.link && !item.link.startsWith('http') && !item.children?.length" :to="item.link" @click="closeMobile" class="mm-item" :class="{'mm-active': route.path===item.link}">
                                    <i class="fas fa-link mm-icon"></i><span>{{ item.title }}</span>
                                </router-link>
                                <a v-else-if="item.link && item.link.startsWith('http') && !item.children?.length" :href="item.link" target="_blank" rel="noopener noreferrer" class="mm-item" @click="closeMobile">
                                    <i class="fas fa-external-link-alt mm-icon"></i><span>{{ item.title }}</span>
                                </a>
                                <MobileAccordion v-else-if="item.children?.length" :item="item" :depth="0" :open-accs="openAccs" :toggle="toggleAccordion" :is-open="isOpen" :close="closeMobile" :route="route" />
                            </template>
                        </div>
                    </div>
                </div>
            </transition>
        </header>

        <div v-if="pengumuman.length && !isHome" class="mt-16 lg:mt-[72px] border-b" style="background:rgba(240,168,0,0.06);border-color:rgba(240,168,0,0.12)">
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

        <footer role="contentinfo" aria-label="Footer" class="relative overflow-hidden">
            <div class="absolute inset-0" style="background:linear-gradient(180deg, #1e40af 0%, #1e3a8a 100%)"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image:linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px),linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px);background-size:60px 60px"></div>
            <div class="relative">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                        <div>
                            <div class="mb-6">
                                <img v-if="setting?.logo" :src="'/upload/settings/' + setting.logo" class="h-16 w-auto object-contain brightness-0 invert opacity-90" :alt="setting?.title || 'Logo BPMP NTB'" loading="lazy" width="64" height="64">
                            </div>
                            <p class="text-sm text-white/80 leading-relaxed mb-2 font-semibold">{{ setting?.title || 'BPMP Provinsi NTB' }}</p>
                            <p class="text-sm text-white/40 leading-relaxed whitespace-pre-line mb-6">{{ setting?.alamat }}</p>
                            <div v-if="setting?.map" class="rounded-xl overflow-hidden border border-white/10">
                                <template v-if="mapLoaded">
                                    <div v-html="setting.map" class="[&_iframe]:w-full [&_iframe]:h-[180px] [&_iframe]:border-0"></div>
                                </template>
                                <button v-else @click="mapLoaded = true" class="w-full py-8 flex flex-col items-center gap-2 text-white/30 hover:text-white/50 transition-colors text-sm">
                                    <i class="fas fa-map-marker-alt text-xl"></i>
                                    <span>Klik untuk memuat peta</span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-white uppercase tracking-[0.15em] mb-6 pb-3 border-b border-white/10">Rubrik</h4>
                            <ul class="space-y-3">
                                <li><router-link to="/post/berita" class="text-sm text-white/50 hover:text-white transition flex items-center gap-2.5 group"><span class="w-1 h-1 rounded-full bg-white/20 group-hover:bg-[var(--color-primary)] transition-colors"></span>Berita</router-link></li>
                                <li><router-link to="/post/artikel" class="text-sm text-white/50 hover:text-white transition flex items-center gap-2.5 group"><span class="w-1 h-1 rounded-full bg-white/20 group-hover:bg-[var(--color-primary)] transition-colors"></span>Artikel</router-link></li>
                                <li><router-link to="/post/jurnal" class="text-sm text-white/50 hover:text-white transition flex items-center gap-2.5 group"><span class="w-1 h-1 rounded-full bg-white/20 group-hover:bg-[var(--color-primary)] transition-colors"></span>Jurnal</router-link></li>
                                <li><router-link to="/post/pengumuman" class="text-sm text-white/50 hover:text-white transition flex items-center gap-2.5 group"><span class="w-1 h-1 rounded-full bg-white/20 group-hover:bg-[var(--color-primary)] transition-colors"></span>Pengumuman</router-link></li>
                                <li><router-link to="/post/unduhan" class="text-sm text-white/50 hover:text-white transition flex items-center gap-2.5 group"><span class="w-1 h-1 rounded-full bg-white/20 group-hover:bg-[var(--color-primary)] transition-colors"></span>Unduhan</router-link></li>
                                <li><router-link to="/post/buletin" class="text-sm text-white/50 hover:text-white transition flex items-center gap-2.5 group"><span class="w-1 h-1 rounded-full bg-white/20 group-hover:bg-[var(--color-primary)] transition-colors"></span>Buletin</router-link></li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-white uppercase tracking-[0.15em] mb-6 pb-3 border-b border-white/10">Statistik</h4>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-white/40">Total Pengunjung</span>
                                    <span class="text-white font-bold bg-white/5 px-3 py-1.5 rounded-lg text-xs border border-white/10">{{ visitorStats?.totalVisitors||0 }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-white/40">Hari Ini</span>
                                    <span class="text-white font-bold bg-white/5 px-3 py-1.5 rounded-lg text-xs border border-white/10">{{ visitorStats?.todayVisitors||0 }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-white/40">Bulan Ini</span>
                                    <span class="text-white font-bold bg-white/5 px-3 py-1.5 rounded-lg text-xs border border-white/10">{{ visitorStats?.thismonthVisitors||0 }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-white/40">Online</span>
                                    <span class="font-bold px-3 py-1.5 rounded-lg text-xs" style="background:rgba(0,144,216,0.15);color:var(--color-primary-light);border:1px solid rgba(0,144,216,0.2)">{{ visitorStats?.onlineVisitors||0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-white uppercase tracking-[0.15em] mb-6 pb-3 border-b border-white/10">Hubungi Kami</h4>
                            <ul class="space-y-4 text-sm text-white/50">
                                <li class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-white/5 flex items-center justify-center flex-shrink-0 mt-0.5 border border-white/10"><i class="fas fa-phone-alt text-[11px] text-white/40"></i></div>
                                    <span>{{ setting?.phone || '-' }}</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-white/5 flex items-center justify-center flex-shrink-0 mt-0.5 border border-white/10"><i class="fas fa-envelope text-[11px] text-white/40"></i></div>
                                    <span>{{ setting?.email || '-' }}</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-white/5 flex items-center justify-center flex-shrink-0 mt-0.5 border border-white/10"><i class="fas fa-map-marker-alt text-[11px] text-white/40"></i></div>
                                    <span>{{ setting?.alamat || '-' }}</span>
                                </li>
                            </ul>
                            <div class="flex gap-2.5 mt-6">
                                <a v-if="setting?.facebook" :href="setting.facebook" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-lg flex items-center justify-center bg-white/5 hover:bg-[var(--color-primary)] text-white/40 hover:text-white transition-all duration-300 border border-white/10 hover:border-transparent"><i class="fab fa-facebook-f text-xs"></i></a>
                                <a v-if="setting?.twitter" :href="setting.twitter" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-lg flex items-center justify-center bg-white/5 hover:bg-[var(--color-primary)] text-white/40 hover:text-white transition-all duration-300 border border-white/10 hover:border-transparent"><i class="fab fa-twitter text-xs"></i></a>
                                <a v-if="setting?.instagram" :href="setting.instagram" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-lg flex items-center justify-center bg-white/5 hover:bg-[var(--color-primary)] text-white/40 hover:text-white transition-all duration-300 border border-white/10 hover:border-transparent"><i class="fab fa-instagram text-xs"></i></a>
                                <a v-if="setting?.youtube" :href="setting.youtube" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-lg flex items-center justify-center bg-white/5 hover:bg-red-600 text-white/40 hover:text-white transition-all duration-300 border border-white/10 hover:border-transparent"><i class="fab fa-youtube text-xs"></i></a>
                                <a v-if="setting?.whatsapp" :href="'https://wa.me/'+setting.whatsapp.replace(/[^0-9]/g,'')" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-lg flex items-center justify-center bg-white/5 hover:bg-green-600 text-white/40 hover:text-white transition-all duration-300 border border-white/10 hover:border-transparent"><i class="fab fa-whatsapp text-xs"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="border-t border-white/5">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-2">
                        <p class="text-xs text-white/25">&copy; {{ new Date().getFullYear() }} {{ setting?.title || 'BPMP Provinsi NTB' }}. Kementerian Pendidikan Dasar dan Menengah.</p>
                        <p class="text-xs text-white/15">Republik Indonesia</p>
                    </div>
                </div>
            </div>
        </footer>

        <button v-if="!chatbotLoaded" @click="chatbotLoaded = true" class="chatbot-trigger" aria-label="Buka chatbot Si Intan">
            <img src="/intan.png" alt="INTAN" class="w-9 h-9 rounded-full object-cover" @error="$event.target.style.display='none'" loading="lazy" width="36" height="36">
            <span class="chatbot-trigger-text">Tanya INTAN</span>
        </button>
        <Chatbot v-if="chatbotLoaded" />

        <button v-if="!a11yLoaded" @click="a11yLoaded = true" class="a11y-trigger" aria-label="Buka pengaturan aksesibilitas">
            <i class="fas fa-universal-access"></i>
        </button>
        <AccessibilityWidget v-if="a11yLoaded" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick, defineAsyncComponent } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/bootstrap.js';
import MobileAccordion from '@/components/MobileAccordion.vue';

const Chatbot = defineAsyncComponent(() => import('@/components/Chatbot.vue'));
const AccessibilityWidget = defineAsyncComponent(() => import('@/components/AccessibilityWidget.vue'));

const route = useRoute();
const router = useRouter();
const setting = ref(null);
const profil = ref([]);
const layanan = ref([]);
const pengumuman = ref([]);
const visitorStats = ref(null);
const scrolled = ref(false);
const scrollY = ref(0);
const mobileMenu = ref(false);
const openAccs = ref(new Set());
const mobileWrap = ref(null);
const chatbotLoaded = ref(false);
const a11yLoaded = ref(false);
const mapLoaded = ref(false);

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

const headerStyle = computed(() => {
    const raw = Math.min(scrollY.value / 200, 1);
    const opacity = 0.7 + raw * 0.3;
    const isHome = route.path === '/';
    if (!isHome) {
        return {
            background: 'var(--color-primary)',
            boxShadow: '0 2px 16px rgba(37,99,235,0.25)',
        };
    }
    return {
        background: `rgba(37,99,235,${opacity})`,
        boxShadow: `0 2px 16px rgba(37,99,235,${opacity * 0.2})`,
        backdropFilter: raw < 0.9 ? 'blur(12px)' : 'none',
    };
});

function toggleMobile() {
    mobileMenu.value = !mobileMenu.value;
    if (mobileMenu.value) { document.body.style.overflow = 'hidden'; openAccs.value = new Set(); autoOpenActive(); }
    else { document.body.style.overflow = ''; }
}
function closeMobile() { mobileMenu.value = false; document.body.style.overflow = ''; }
function toggleAccordion(key) { const s = new Set(openAccs.value); s.has(key) ? s.delete(key) : s.add(key); openAccs.value = s; }
function isOpen(key) { return openAccs.value.has(key); }

function autoOpenActive() {
    const path = route.path;
    if (profil.value.some(p => `/post/profil/${p.id}/${p.slug}` === path)) openAccs.value.add('profil');
    if (layanan.value.some(s => `/layanan/${s.id}/${s.slug}` === path)) openAccs.value.add('layanan');
    if (['/post/berita','/post/artikel','/post/jurnal','/post/pengumuman','/post/unduhan','/post/buletin'].includes(path)) openAccs.value.add('rubrik');
    if (['/post/galeri','/post/kliping'].includes(path)) openAccs.value.add('galeri');
    const check = (items) => { for (const item of items) { if (item.link === path) return true; if (item.children?.length && check(item.children)) { openAccs.value.add('nav_' + item.title); return true; } } return false; };
    check(navItems.value);
}

watch(() => route.path, () => closeMobile());
function onKeydown(e) { if (e.key === 'Escape' && mobileMenu.value) closeMobile(); }
function onScroll() { scrollY.value = window.scrollY; scrolled.value = window.scrollY > 60; }

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
    const [s, b, v, l] = results;
    if (s.status==='fulfilled') setting.value = s.value.data;
    if (b.status==='fulfilled') { profil.value = b.value.data.profil||[]; pengumuman.value = b.value.data.pengumuman||[]; }
    if (v.status==='fulfilled') visitorStats.value = v.value.data;
    if (l.status==='fulfilled') layanan.value = l.value.data||[];
});

onUnmounted(() => { window.removeEventListener('scroll', onScroll); window.removeEventListener('keydown', onKeydown); document.body.style.overflow = ''; });
</script>

<style scoped>
.nav-link {
    display: inline-flex; align-items: center; padding: 8px 16px;
    font-size: 14px; font-weight: 500; border-radius: 10px;
    color: rgba(255,255,255,0.8); transition: all 0.25s ease;
    text-decoration: none; position: relative;
}
.nav-link::after {
    content: ''; position: absolute; bottom: 2px; left: 50%; width: 0; height: 2px;
    background: #fff; border-radius: 1px;
    transition: all 0.3s cubic-bezier(0.4,0,0.2,1); transform: translateX(-50%);
}
.nav-link:hover { color: #fff; background: rgba(255,255,255,0.1); }
.nav-link:hover::after { width: 20px; }
.nav-link-active { color: #fff; font-weight: 600; }
.nav-link-active::after { width: 20px; }

.group:hover > .dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
.dropdown .group:hover > .dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
.dd-flip { left: auto !important; right: 100% !important; margin-left: 0 !important; margin-right: 4px !important; }
.dropdown {
    position: absolute; left: 0; top: 100%; min-width: 220px;
    background: rgba(255,255,255,0.95); backdrop-filter: blur(24px) saturate(180%);
    -webkit-backdrop-filter: blur(24px) saturate(180%);
    border-radius: 16px; padding: 6px;
    box-shadow: 0 16px 48px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.04);
    opacity: 0; visibility: hidden; transform: translateY(8px);
    transition: all 0.25s cubic-bezier(0.4,0,0.2,1); z-index: 50;
}
.dropdown::before {
    content: ''; position: absolute; top: -12px; left: 0; right: 0; height: 12px;
}
.dropdown-item {
    display: block; padding: 10px 14px; border-radius: 10px;
    font-size: 13px; font-weight: 500; color: var(--color-text-primary);
    transition: all 0.15s; text-decoration: none;
}
.dropdown-item:hover { background: rgba(37,99,235,0.06); color: var(--color-primary); padding-left: 18px; }

.chatbot-trigger {
    position: fixed; bottom: 24px; right: 24px; z-index: 40;
    display: flex; align-items: center; gap: 10px;
    padding: 12px 20px 12px 12px; border-radius: 999px;
    background: var(--color-primary);
    color: white; box-shadow: 0 8px 32px rgba(37,99,235,0.25);
    cursor: pointer; border: none; font-family: 'Inter', sans-serif;
    font-size: 13px; font-weight: 600;
    transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
}
.chatbot-trigger:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 12px 40px rgba(37,99,235,0.35); }
.chatbot-trigger-text { display: none; }
@media (min-width: 640px) { .chatbot-trigger-text { display: inline; } }

.a11y-trigger {
    position: fixed; bottom: 24px; left: 24px; z-index: 40;
    width: 46px; height: 46px; border-radius: 999px;
    background: var(--color-secondary); color: white;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; border: 1px solid rgba(255,255,255,0.1); font-size: 18px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.3);
    transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
}
.a11y-trigger:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.4); }

@keyframes ticker { 0%{transform:translateX(100%)} 100%{transform:translateX(-100%)} }
</style>

<style>
.mm-item {
    display: flex; align-items: center; gap: 12px;
    padding: 0 20px; height: 48px; min-height: 48px;
    font-size: 15px; font-weight: 500; color: #1f2937;
    transition: all .2s ease; text-decoration: none; cursor: pointer;
    border: none; background: none; width: 100%; text-align: left;
}
.mm-item:hover, .mm-item:focus { background: rgba(37,99,235,0.04); color: var(--color-primary); }
.mm-active { color: var(--color-primary) !important; font-weight: 600; background: rgba(37,99,235,0.04); }
.mm-icon { width: 20px; text-align: center; font-size: 14px; opacity: 0.4; flex-shrink: 0; }
.mm-parent { font-weight: 600; }
.mm-child { padding-left: 52px; height: 44px; min-height: 44px; font-size: 14px; color: #64748b; position: relative; }
.mm-child::before { content: ''; position: absolute; left: 36px; top: 50%; width: 4px; height: 4px; border-radius: 50%; background: #cbd5e1; transform: translateY(-50%); transition: all .2s; }
.mm-child:hover { color: var(--color-primary); }
.mm-child:hover::before { background: var(--color-primary); transform: translateY(-50%) scale(1.5); }
.mm-sub { background: #f8fafc; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; }
.mm-sub .mm-sub { background: #f1f5f9; }
.mm-sub .mm-child { padding-left: 72px; }
.mm-sub .mm-child::before { left: 56px; }
.mm-sub .mm-sub .mm-child { padding-left: 92px; }
.mm-sub .mm-sub .mm-child::before { left: 76px; }
.mm-chevron { font-size: 10px; opacity: 0.3; transition: transform .3s cubic-bezier(0.4,0,0.2,1); flex-shrink: 0; }
.mm-chevron-open { transform: rotate(180deg); }
.mm-overlay-enter-active { transition: opacity .3s ease; }
.mm-overlay-leave-active { transition: opacity .2s ease; }
.mm-overlay-enter-from, .mm-overlay-leave-to { opacity: 0; }
.skip-link { position: fixed; top: -100px; left: 16px; z-index: 99999; background: var(--color-primary, #2563eb); color: #fff; padding: 12px 20px; border-radius: 0 0 12px 12px; font-size: 14px; font-weight: 600; text-decoration: none; transition: top .2s; }
.skip-link:focus { top: 0; }
</style>
