<template>
    <PublicLayout>

        <section class="hero-section" @mousemove="onHeroMouseMove" @mouseleave="onHeroMouseLeave">
            <div class="hero-bg-layer" :style="heroBgStyle">
                <img src="/kantor_depan.jpg" alt="Kantor BPMP Provinsi NTB" class="w-full h-[130%] object-cover" fetchpriority="high" width="1920" height="1080" @error="$event.target.style.display='none'">
            </div>
            <div class="hero-overlay"></div>
            <div class="hero-decor" :style="heroDecorStyle">
                <div class="hero-orb hero-orb-1"></div>
                <div class="hero-orb hero-orb-2"></div>
                <div class="hero-orb hero-orb-3"></div>
                <div class="hero-grid"></div>
            </div>
            <div class="hero-slider" :style="{transform:`translateX(-${slide*100}%)`}">
                <div class="hero-slide">
                    <div class="hero-content">
                        <div class="hero-badge"><span class="hero-badge-dot"></span>KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH</div>
                        <h1 class="hero-title">BPMP<br><span class="hero-title-accent">Provinsi NTB</span></h1>
                        <p class="hero-desc">Bersama Menjamin Mutu, Melayani Sepenuh Hati. Balai Penjaminan Mutu Pendidikan Provinsi Nusa Tenggara Barat.</p>
                        <div class="hero-cta">
                            <router-link to="/post/berita" class="btn-primary"><i class="fas fa-newspaper mr-2"></i>Berita Terkini</router-link>
                            <a href="#layanan" class="btn-secondary"><i class="fas fa-th-large mr-2"></i>Layanan Kami</a>
                        </div>
                    </div>
                </div>
                <div v-for="s in sliders" :key="s.id" class="hero-slide">
                    <img :src="s.image_url" :alt="s.title || 'Slider BPMP NTB'" class="absolute inset-0 w-full h-full object-cover" loading="lazy" width="1920" height="1080">
                    <div v-if="s.title" class="hero-overlay"></div>
                    <div v-if="s.title" class="hero-content">
                        <h1 class="hero-title">{{ s.title }}</h1>
                        <p v-if="s.description" class="hero-desc">{{ s.description }}</p>
                    </div>
                </div>
            </div>
            <button @click="slide=(slide-1+total)%total" class="hero-nav hero-nav-prev" aria-label="Slide sebelumnya"><i class="fas fa-chevron-left"></i></button>
            <button @click="slide=(slide+1)%total" class="hero-nav hero-nav-next" aria-label="Slide berikutnya"><i class="fas fa-chevron-right"></i></button>
            <div class="hero-dots"><button v-for="i in total" :key="i" @click="slide=i-1" class="hero-dot" :class="{'hero-dot-active': i-1===slide}" :aria-label="'Slide '+i"></button></div>
            <a href="#stats" class="hero-scroll" aria-label="Scroll ke bawah"><i class="fas fa-chevron-down"></i></a>
            <div class="hero-wave"><svg viewBox="0 0 1440 100" fill="none" preserveAspectRatio="none"><path d="M0,60 C360,100 720,20 1080,60 C1260,80 1380,70 1440,60 L1440,100 L0,100 Z" fill="white"/></svg></div>
        </section>

        <section id="stats" class="py-20 bg-white relative overflow-hidden">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] rounded-full opacity-[0.04] animate-float-slow" style="background:radial-gradient(circle, var(--color-primary) 0%, transparent 70%);filter:blur(100px)"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="(stat, idx) in statsData" :key="stat.label" class="text-center reveal" :class="[`delay-${idx+1}`, {'is-visible': vis.stats}]">
                        <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:linear-gradient(135deg, rgba(37,99,235,0.08) 0%, rgba(66,165,245,0.04) 100%)">
                            <i :class="stat.icon" class="text-xl" style="color:var(--color-primary)"></i>
                        </div>
                        <div class="text-3xl lg:text-4xl font-extrabold mb-1 counter-value" style="color:var(--color-primary)" :data-target="stat.value">0</div>
                        <div class="text-sm font-medium" style="color:var(--color-text-secondary)">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section id="kinerja" class="py-24 bg-white relative overflow-hidden">
            <div class="divider-gradient mb-24"></div>
            <div class="absolute -bottom-32 -right-32 w-[500px] h-[500px] rounded-full opacity-[0.03] animate-float" style="background:radial-gradient(circle, var(--color-primary-light) 0%, transparent 70%);filter:blur(80px)"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="text-center mb-16 reveal" :class="{'is-visible': vis.kinerja}">
                    <div class="section-label justify-center">Dokumen</div>
                    <h2 class="section-title">Kinerja Lembaga</h2>
                    <p class="section-subtitle mx-auto">Dokumen perencanaan dan pelaporan kinerja BPMP Provinsi NTB</p>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto">
                    <router-link v-for="(item, idx) in kinerja" :key="item.label" :to="item.to" class="card-glass-light p-8 text-center group reveal" :class="[`delay-${idx+1}`, {'is-visible': vis.kinerja}]">
                        <div class="w-16 h-16 rounded-2xl mx-auto mb-5 flex items-center justify-center transition-all duration-500 group-hover:scale-110 group-hover:shadow-lg" style="background:linear-gradient(135deg, rgba(37,99,235,0.08) 0%, rgba(66,165,245,0.03) 100%)">
                            <img :src="item.img" :alt="item.label" class="h-9 w-9 object-contain" loading="lazy" width="36" height="36" @error="$event.target.style.display='none'">
                        </div>
                        <div class="text-sm font-semibold" style="color:#455A64">{{ item.label }}</div>
                    </router-link>
                    <a :href="setting?.ikm_link || '#'" target="_blank" rel="noopener noreferrer" class="card-glass-light p-8 text-center group reveal delay-4" :class="{'is-visible': vis.kinerja}" style="border:2px solid rgba(240,168,0,0.12)">
                        <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:rgba(240,168,0,0.06)">
                            <img src="/ikm-2025.png" alt="Indeks Kepuasan Masyarakat" class="h-11 w-18 object-contain" loading="lazy" width="72" height="44" @error="$event.target.style.display='none'">
                        </div>
                        <div class="text-sm font-semibold mb-1" style="color:#455A64">Indeks Kepuasan Masyarakat</div>
                        <div class="text-4xl font-extrabold" style="color:var(--color-accent)">{{ setting?.ikm_score || '0' }}</div>
                        <div class="text-xs mt-1" style="color:var(--color-text-secondary)">{{ setting?.ikm_period || '' }}</div>
                        <div class="text-xs mt-3 font-semibold" style="color:var(--color-primary)">Beri Penilaian <i class="fas fa-arrow-right text-[9px] ml-1"></i></div>
                    </a>
                </div>
            </div>
        </section>

        <section id="layanan" class="py-28 relative overflow-hidden" style="background:linear-gradient(180deg, #EFF6FF 0%, #DBEAFE 50%, #EFF6FF 100%)">
            <div class="absolute inset-0 opacity-[0.06]" style="background-image:radial-gradient(rgba(37,99,235,0.4) 1px, transparent 1px);background-size:32px 32px"></div>
            <div class="absolute top-20 right-[10%] w-[500px] h-[500px] rounded-full opacity-[0.08] animate-float-slow" style="background:radial-gradient(circle, var(--color-primary-light) 0%, transparent 70%);filter:blur(80px)"></div>
            <div class="absolute bottom-20 left-[5%] w-[400px] h-[400px] rounded-full opacity-[0.06] animate-float" style="background:radial-gradient(circle, var(--color-primary) 0%, transparent 70%);filter:blur(60px)"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="text-center mb-16 reveal" :class="{'is-visible': vis.layanan}">
                    <div class="section-label justify-center">Layanan</div>
                    <h2 class="section-title">Layanan BPMP Provinsi NTB</h2>
                    <p class="section-subtitle mx-auto">Berbagai layanan penjaminan mutu pendidikan untuk masyarakat NTB</p>
                </div>
                <div class="max-w-4xl mx-auto mb-16 reveal delay-1" :class="{'is-visible': vis.layanan}">
                    <div class="relative rounded-2xl overflow-hidden" style="background:linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)">
                        <div class="absolute inset-0 opacity-[0.06]" style="background-image:radial-gradient(rgba(255,255,255,0.4) 1px, transparent 1px);background-size:20px 20px"></div>
                        <div class="absolute top-4 right-6 text-[120px] leading-none font-black opacity-[0.06] select-none" style="color:white">"</div>
                        <div class="relative px-8 py-12 md:px-14 md:py-16 text-center">
                            <div class="w-16 h-16 rounded-2xl mx-auto mb-6 flex items-center justify-center" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
                                <i class="fas fa-shield-halved text-2xl text-white"></i>
                            </div>
                            <div class="inline-flex items-center gap-2 mb-4 px-4 py-1.5 rounded-full text-[11px] font-bold tracking-[0.15em] uppercase text-white/80" style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15)">
                                <i class="fas fa-award text-[10px]"></i>Komitmen Pelayanan
                            </div>
                            <h3 class="text-2xl md:text-3xl font-extrabold text-white mb-6 tracking-tight">MAKLUMAT PELAYANAN</h3>
                            <div class="max-w-2xl mx-auto">
                                <p class="text-base md:text-lg leading-relaxed text-white/85 font-medium italic">"Dengan ini, kami sanggup menyelenggarakan pelayanan sesuai standar pelayanan yang telah ditetapkan dan apabila tidak menepati janji ini, kami siap menerima sanksi sesuai peraturan perundang-undangan yang berlaku"</p>
                            </div>
                            <div class="mt-8 flex items-center justify-center gap-6 text-white/50 text-xs font-semibold">
                                <span class="flex items-center gap-2"><i class="fas fa-check-circle text-white/40"></i>Standar Pelayanan</span>
                                <span class="w-1 h-1 rounded-full bg-white/20"></span>
                                <span class="flex items-center gap-2"><i class="fas fa-check-circle text-white/40"></i>Jaminan Mutu</span>
                                <span class="w-1 h-1 rounded-full bg-white/20"></span>
                                <span class="flex items-center gap-2"><i class="fas fa-check-circle text-white/40"></i>Akuntabilitas</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <component v-for="(svc, idx) in layanan.slice(0, 6)" :key="svc.id" :is="svc.link_type==='external'?'a':'router-link'" :href="svc.link_type==='external'?svc.link_url:undefined" :to="svc.link_type!=='external'?`/layanan/${svc.id}/${svc.slug}`:undefined" :target="svc.link_type==='external'?'_blank':undefined" :rel="svc.link_type==='external'?'noopener noreferrer':undefined" class="card p-7 group reveal" :class="[`delay-${Math.min(idx+1,5)}`, {'is-visible': vis.layanan}]">
                        <div class="flex items-start gap-5">
                            <div class="w-14 h-14 rounded-xl flex-shrink-0 flex items-center justify-center transition-all duration-500 group-hover:scale-110 group-hover:shadow-md" style="background:rgba(37,99,235,0.08);border:1px solid rgba(37,99,235,0.1)">
                                <img v-if="svc.image" :src="'/upload/layanans/'+svc.image" :alt="svc.title" class="h-8 w-8 object-contain" loading="lazy" width="32" height="32" @error="$event.target.style.display='none'">
                                <i v-else class="fas fa-concierge-bell text-lg" style="color:var(--color-primary)"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-bold mb-2 group-hover:text-[var(--color-primary)] transition-colors" style="color:#37474F">{{ svc.title }}</h3>
                                <p class="text-xs leading-relaxed line-clamp-2" style="color:var(--color-text-secondary)">Layanan penjaminan mutu pendidikan dari BPMP Provinsi NTB</p>
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold mt-3 opacity-0 group-hover:opacity-100 transition-all duration-300" style="color:var(--color-primary)">Selengkapnya <i class="fas fa-arrow-right text-[9px]"></i></span>
                            </div>
                        </div>
                    </component>
                </div>
                <div v-if="layanan.length > 6" class="text-center mt-10 reveal delay-5" :class="{'is-visible': vis.layanan}">
                    <router-link to="/layanan" class="btn-outline py-3.5 px-8 text-sm">Lihat Semua Layanan <i class="fas fa-arrow-right ml-2 text-[10px]"></i></router-link>
                </div><br>
                <div class="divider-gradient mb-19"></div>
                <div class="absolute -top-32 -left-32 w-[400px] h-[400px] rounded-full opacity-[0.04] animate-float" style="background:radial-gradient(circle, var(--color-primary) 0%, transparent 70%);filter:blur(80px)"></div>
                <div v-if="setting?.silamo_link" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                    <div class="card p-8 lg:p-10 flex flex-col md:flex-row items-center gap-8 reveal-scale" :class="{'is-visible': vis.silamo}" style="border:1px solid rgba(37,99,235,0.1)">
                        <div class="flex-shrink-0">
                            <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='+encodeURIComponent(setting.silamo_link)" alt="QR Zoom SILAMO" class="w-44 h-44 object-contain rounded-xl" style="border:2px solid rgba(37,99,235,0.08)" loading="lazy" width="176" height="176">
                        </div>
                        <div class="flex-1 text-left">
                            <div class="font-bold text-xl mb-3 flex items-center gap-2.5" style="color:#37474F">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background:rgba(37,99,235,0.08)"><i class="fas fa-video" style="color:var(--color-primary)"></i></div>
                                {{ setting.silamo_title || 'SILAMO (Sistem Layanan Melalui Online)' }}
                                <span class="hidden md:inline text-sm font-medium" style="color:var(--color-text-secondary)">{{ setting.silamo_subtitle || 'BPMP Provinsi NTB' }}</span>
                            </div>
                            <div class="mb-1.5 text-sm" style="color:var(--color-text-secondary)"><span class="font-semibold" style="color:#455A64">{{ setting.silamo_schedule || 'Senin s.d Jumat: 09.00 s.d. 11.00 WITA' }}</span></div>
                            <div class="mb-1.5 text-sm" style="color:var(--color-text-secondary)"><span class="font-semibold" style="color:#455A64">ID:</span> {{ setting.silamo_meeting_id || '349 664 0348' }}</div>
                            <div class="mb-1.5 text-sm" style="color:var(--color-text-secondary)"><span class="font-semibold" style="color:#455A64">Sandi:</span> {{ setting.silamo_password || 'ultbpmpntb' }}</div>
                            <div class="mt-3"><a :href="setting.silamo_link" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold underline" style="color:var(--color-primary)">{{ setting.silamo_link }}</a></div>
                            <div class="mt-2 text-xs" style="color:var(--color-text-secondary)">Scan QR atau klik link untuk bergabung Zoom SILA-MO.</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- <section v-if="setting?.silamo_link" class="py-24 bg-white relative overflow-hidden" data-silamo>
        </section> -->

        <section class="py-24 bg-white relative overflow-hidden" data-posts>
            <!-- <div class="divider-gradient mb-24"></div> -->
            <div class="absolute bottom-0 right-0 w-[500px] h-[500px] rounded-full opacity-[0.03] animate-float-slow" style="background:radial-gradient(circle, var(--color-primary-light) 0%, transparent 70%);filter:blur(100px)"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="flex items-end justify-between mb-12 reveal" :class="{'is-visible': vis.posts}">
                    <div>
                        <div class="section-label">Berita</div>
                        <h2 class="section-title">Postingan Terkini</h2>
                        <p class="section-subtitle">Berita dan artikel terbaru dari BPMP NTB</p>
                    </div>
                    <router-link to="/post/berita" class="btn-outline hidden md:inline-flex py-3 px-7 text-xs">Lihat Semua <i class="fas fa-arrow-right ml-2 text-[10px]"></i></router-link>
                </div>
                <div v-if="lastPost.length === 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="i in 3" :key="i" class="card overflow-hidden"><div class="skeleton h-56"></div><div class="p-6 space-y-3"><div class="skeleton h-3 w-24"></div><div class="skeleton h-5 w-full"></div><div class="skeleton h-4 w-3/4"></div></div></div>
                </div>
                <div v-else-if="lastPost.length >= 3" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <router-link :to="`/post/${lastPost[0].jenis}/${lastPost[0].id}/${lastPost[0].slug}`" class="lg:col-span-7 card overflow-hidden group reveal" :class="{'is-visible': vis.posts}">
                        <div class="h-72 lg:h-[400px] bg-gray-100 relative overflow-hidden">
                            <img v-if="lastPost[0].image_url" :src="lastPost[0].image_url" :alt="lastPost[0].title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy" @error="handleThumbError($event,lastPost[0])">
                            <div v-else class="w-full h-full flex items-center justify-center bg-gray-50"><i class="fas fa-newspaper text-5xl text-gray-200"></i></div>
                            <div class="absolute top-5 left-5 badge badge-primary shadow-sm">{{ lastPost[0].kategori }}</div>
                        </div>
                        <div class="p-7">
                            <div class="flex items-center gap-3 text-xs mb-3" style="color:var(--color-text-secondary)">
                                <span class="flex items-center gap-1"><i class="fas fa-user text-[10px]"></i>{{ lastPost[0].writer }}</span>
                                <span class="text-gray-300">·</span>
                                <span class="flex items-center gap-1"><i class="fas fa-calendar text-[10px]"></i>{{ formatDate(lastPost[0].tanggal) }}</span>
                            </div>
                            <h3 class="text-2xl font-bold mb-3 group-hover:text-[var(--color-primary)] transition-colors leading-snug" style="color:#263238">{{ lastPost[0].title }}</h3>
                            <p class="text-sm leading-relaxed line-clamp-3" style="color:var(--color-text-secondary)">{{ lastPost[0].teaser }}</p>
                        </div>
                    </router-link>
                    <div class="lg:col-span-5 flex flex-col gap-5">
                        <router-link v-for="(item, idx) in lastPost.slice(1, 4)" :key="item.id+item.jenis" :to="`/post/${item.jenis}/${item.id}/${item.slug}`" class="card overflow-hidden group flex gap-5 p-5 reveal" :class="[`delay-${idx+1}`, {'is-visible': vis.posts}]">
                            <div class="w-32 h-28 flex-shrink-0 bg-gray-100 rounded-xl overflow-hidden">
                                <img v-if="item.image_url" :src="item.image_url" :alt="item.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" @error="handleThumbError($event,item)">
                                <div v-else class="w-full h-full flex items-center justify-center bg-gray-50"><i class="fas fa-newspaper text-2xl text-gray-200"></i></div>
                            </div>
                            <div class="flex-1 min-w-0 flex flex-col justify-center">
                                <div class="flex items-center gap-2 text-[11px] mb-2" style="color:var(--color-text-secondary)"><span class="badge badge-primary text-[10px]">{{ item.kategori }}</span><span>{{ formatDate(item.tanggal) }}</span></div>
                                <h4 class="text-sm font-bold line-clamp-2 group-hover:text-[var(--color-primary)] transition-colors leading-snug" style="color:#37474F">{{ item.title }}</h4>
                            </div>
                        </router-link>
                    </div>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <router-link v-for="item in lastPost" :key="item.id+item.jenis" :to="`/post/${item.jenis}/${item.id}/${item.slug}`" class="card overflow-hidden group">
                        <div class="h-56 bg-gray-100 relative overflow-hidden">
                            <img v-if="item.image_url" :src="item.image_url" :alt="item.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" @error="handleThumbError($event,item)">
                            <div v-else class="w-full h-full flex items-center justify-center bg-gray-50"><i class="fas fa-newspaper text-4xl text-gray-200"></i></div>
                            <div class="absolute top-4 left-4 badge badge-primary shadow-sm">{{ item.kategori }}</div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-3 text-xs mb-3" style="color:var(--color-text-secondary)"><span class="flex items-center gap-1"><i class="fas fa-user text-[10px]"></i>{{ item.writer }}</span><span class="text-gray-300">·</span><span class="flex items-center gap-1"><i class="fas fa-calendar text-[10px]"></i>{{ formatDate(item.tanggal) }}</span></div>
                            <h3 class="text-base font-bold mb-2 line-clamp-2 group-hover:text-[var(--color-primary)] transition-colors leading-snug" style="color:#37474F">{{ item.title }}</h3>
                            <p class="text-sm leading-relaxed line-clamp-3 mb-4" style="color:var(--color-text-secondary)">{{ item.teaser }}</p>
                            <span class="inline-flex items-center gap-1.5 text-sm font-bold group-hover:gap-2.5 transition-all" style="color:var(--color-primary)">Baca selengkapnya <i class="fas fa-arrow-right text-[10px]"></i></span>
                        </div>
                    </router-link>
                </div>
                <div class="text-center mt-8 md:hidden reveal" :class="{'is-visible': vis.posts}"><router-link to="/post/berita" class="btn-outline py-3 px-8 text-xs">Lihat Semua <i class="fas fa-arrow-right ml-2 text-[10px]"></i></router-link></div>
            </div>
        </section>

        <section class="py-24 bg-white relative overflow-hidden" data-links>
            <div class="divider-gradient mb-24"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="text-center mb-14 reveal" :class="{'is-visible': vis.links}">
                    <div class="section-label justify-center">Tautan</div>
                    <h2 class="section-title">Tautan Penting</h2>
                    <p class="section-subtitle mx-auto">Link terkait layanan pendidikan nasional</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 max-w-4xl mx-auto">
                    <a v-for="(item, idx) in externalLinks" :key="item.id" :href="item.link" target="_blank" rel="noopener noreferrer" class="flex items-center gap-4 px-5 py-4 rounded-xl border transition-all duration-300 hover:-translate-y-1 hover:shadow-md group reveal" :class="[`delay-${Math.min(idx+1,5)}`, {'is-visible': vis.links}]" style="border-color:#e0e0e0;background:white">
                        <span class="text-2xl flex-shrink-0 w-11 h-11 flex items-center justify-center rounded-lg" style="background:#EFF6FF">{{ item.images }}</span>
                        <span class="text-sm font-semibold leading-snug group-hover:text-[var(--color-primary)] transition-colors" style="color:#37474F">{{ item.title }}</span>
                        <i class="fas fa-arrow-up-right-from-square text-[10px] ml-auto opacity-0 group-hover:opacity-40 transition-opacity" style="color:var(--color-text-secondary)"></i>
                    </a>
                </div>
            </div>
        </section>

    </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, reactive, nextTick } from 'vue';
import api from '@/bootstrap.js';
import PublicLayout from '@/layouts/PublicLayout.vue';

const sliders = ref([]); const lastPost = ref([]); const externalLinks = ref([]); const setting = ref(null); const layanan = ref([]); const slide = ref(0); let timer = null;
const total = computed(() => sliders.value.length + 1);
const scrollY = ref(0); const mouseX = ref(0); const mouseY = ref(0);
const prefersReducedMotion = typeof window !== 'undefined' ? window.matchMedia('(prefers-reduced-motion: reduce)').matches : false;

const heroBgStyle = computed(() => prefersReducedMotion ? {} : { transform: `translate3d(0, ${scrollY.value * 0.35}px, 0) scale(1.1)`, willChange: 'transform' });
const heroDecorStyle = computed(() => {
    if (prefersReducedMotion) return {};
    const mx = (mouseX.value - 0.5) * 20, my = (mouseY.value - 0.5) * 20;
    return { transform: `translate3d(${mx}px, ${scrollY.value * 0.15 + my}px, 0)`, willChange: 'transform' };
});
function onHeroMouseMove(e) { if (prefersReducedMotion) return; const r = e.currentTarget.getBoundingClientRect(); mouseX.value = (e.clientX - r.left) / r.width; mouseY.value = (e.clientY - r.top) / r.height; }
function onHeroMouseLeave() { mouseX.value = 0.5; mouseY.value = 0.5; }

const bulanIndo=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
function formatDate(tgl){if(!tgl)return'-';const d=new Date(tgl);return isNaN(d)?tgl:`${d.getDate()} ${bulanIndo[d.getMonth()]} ${d.getFullYear()}`;}
function handleThumbError(e,item){if(item.image_fallback&&e.target.src!==item.image_fallback)e.target.src=item.image_fallback;else e.target.style.display='none';}
const kinerja=[{label:'Laporan Kinerja',img:'/lakin.png',to:'/post/lakin'},{label:'Rencana Strategis',img:'/renstra.png',to:'/post/renstra'},{label:'Perjanjian Kinerja',img:'/handshake.png',to:'/post/perjanjian_kinerja'}];

const visitorStats = ref(null);
const statsData = computed(() => [
    { icon: 'fas fa-users', value: visitorStats.value?.totalVisitors || 0, label: 'Total Pengunjung' },
    { icon: 'fas fa-concierge-bell', value: layanan.value?.length || 0, label: 'Layanan Tersedia' },
    { icon: 'fas fa-star', value: parseFloat(setting.value?.ikm_score) || 0, label: 'Skor IKM' },
    { icon: 'fas fa-globe', value: visitorStats.value?.onlineVisitors || 0, label: 'Online Saat Ini' },
]);

const vis = reactive({ stats: false, kinerja: false, layanan: false, silamo: false, posts: false, links: false });
let observers = []; let counterDone = false;

function animateCounters() {
    if (counterDone) return; counterDone = true;
    nextTick(() => { document.querySelectorAll('.counter-value[data-target]').forEach(el => {
        const target = parseFloat(el.dataset.target); if (!target) return;
        const isFloat = target % 1 !== 0, duration = 2000, startTime = performance.now();
        (function update(now) { const p = Math.min((now - startTime) / duration, 1); const e = 1 - Math.pow(1 - p, 3); el.textContent = isFloat ? (target * e).toFixed(1) : Math.round(target * e).toLocaleString('id-ID'); if (p < 1) requestAnimationFrame(update); })(startTime);
    }); });
}

function setupReveal() {
    if (prefersReducedMotion) { Object.keys(vis).forEach(k => vis[k] = true); animateCounters(); return; }
    const targets = { '#stats': 'stats', '#kinerja': 'kinerja', '#layanan': 'layanan', '[data-silamo]': 'silamo', '[data-posts]': 'posts', '[data-links]': 'links' };
    for (const [sel, key] of Object.entries(targets)) {
        const el = document.querySelector(sel); if (!el) { vis[key] = true; continue; }
        const obs = new IntersectionObserver(([entry]) => { if (entry.isIntersecting) { vis[key] = true; if (key === 'stats') animateCounters(); obs.disconnect(); } }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
        obs.observe(el); observers.push(obs);
    }
}

function onScroll() { scrollY.value = window.scrollY; }

onMounted(async () => {
    window.addEventListener('scroll', onScroll, { passive: true });
    const [bd, st, lyn, vs] = await Promise.allSettled([api.get('/beranda'), api.get('/settings'), api.get('/layanans-public'), api.get('/visitor-stats')]);
    if (bd.status==='fulfilled') { sliders.value = bd.value.data.sliders||[]; lastPost.value = bd.value.data.lastPost||[]; externalLinks.value = bd.value.data.externalLinks||[]; }
    if (st.status==='fulfilled') setting.value = st.value.data;
    if (lyn.status==='fulfilled') layanan.value = lyn.value.data||[];
    if (vs.status==='fulfilled') visitorStats.value = vs.value.data;
    timer = setInterval(() => { slide.value = (slide.value + 1) % total.value; }, 7000);
    setTimeout(setupReveal, 150);
});
onUnmounted(() => { if (timer) clearInterval(timer); observers.forEach(o => o.disconnect()); window.removeEventListener('scroll', onScroll); });
</script>

<style scoped>
.hero-section { position: relative; width: 100%; overflow: hidden; height: min(100svh, 960px); min-height: 600px; }
.hero-bg-layer { position: absolute; inset: -20%; will-change: transform; }
.hero-overlay { position: absolute; inset: 0; background: linear-gradient(135deg, rgba(30,64,175,0.92) 0%, rgba(37,99,235,0.7) 40%, rgba(96,165,250,0.4) 100%); }
.hero-decor { position: absolute; inset: 0; pointer-events: none; will-change: transform; }
.hero-orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.15; }
.hero-orb-1 { width: 600px; height: 600px; top: -15%; right: -10%; background: radial-gradient(circle, var(--color-primary-light) 0%, transparent 70%); animation: float-slow 12s ease-in-out infinite; }
.hero-orb-2 { width: 400px; height: 400px; bottom: 10%; left: -5%; background: radial-gradient(circle, #93C5FD 0%, transparent 70%); animation: float 10s ease-in-out infinite reverse; }
.hero-orb-3 { width: 250px; height: 250px; top: 40%; left: 45%; background: radial-gradient(circle, var(--color-accent) 0%, transparent 70%); animation: float-slow 14s ease-in-out infinite; opacity: 0.08; }
.hero-grid { position: absolute; inset: 0; opacity: 0.04; background-image: linear-gradient(rgba(255,255,255,0.2) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.2) 1px, transparent 1px); background-size: 80px 80px; }
.hero-slider { position: absolute; inset: 0; display: flex; transition: transform 0.8s cubic-bezier(0.4,0,0.2,1); }
.hero-slide { flex-shrink: 0; width: 100%; height: 100%; position: relative; }
.hero-content { position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: center; z-index: 5; max-width: 1280px; margin: 0 auto; padding: 0 24px; }
@media (min-width: 640px) { .hero-content { padding: 0 32px; } }
@media (min-width: 1024px) { .hero-content { padding: 0 48px; } }
.hero-badge { display: inline-flex; align-items: center; gap: 10px; padding: 10px 20px; border-radius: 999px; font-size: 11px; font-weight: 600; letter-spacing: 0.12em; color: rgba(255,255,255,0.85); margin-bottom: 28px; width: fit-content; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); backdrop-filter: blur(8px); animation: fadeIn 0.8s ease-out 0.2s both; }
.hero-badge-dot { width: 8px; height: 8px; border-radius: 50%; background: #66BB6A; box-shadow: 0 0 8px rgba(102,187,106,0.5); }
.hero-title { font-size: clamp(2.5rem, 6vw, 4.5rem); font-weight: 900; color: rgba(255,255,255,0.95); line-height: 1.05; letter-spacing: -0.04em; margin-bottom: 24px; text-shadow: 0 4px 30px rgba(0,0,0,0.15); animation: slideUp 0.8s ease-out 0.3s both; max-width: 700px; }
.hero-title-accent { color: rgba(255,255,255,0.7); }
.hero-desc { font-size: clamp(1rem, 2vw, 1.2rem); color: rgba(255,255,255,0.6); line-height: 1.8; margin-bottom: 40px; max-width: 560px; font-weight: 400; animation: fadeIn 0.8s ease-out 0.5s both; }
.hero-cta { display: flex; flex-wrap: wrap; gap: 16px; animation: fadeIn 0.8s ease-out 0.7s both; }
.hero-nav { position: absolute; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: rgba(255,255,255,0.1); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.15); color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; font-size: 14px; }
.hero-nav:hover { background: rgba(255,255,255,0.2); border-color: rgba(255,255,255,0.35); transform: translateY(-50%) scale(1.05); }
.hero-nav-prev { left: 20px; } .hero-nav-next { right: 20px; }
@media (min-width: 1024px) { .hero-nav-prev { left: 32px; } .hero-nav-next { right: 32px; } }
.hero-dots { position: absolute; bottom: 80px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px; z-index: 10; }
.hero-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.25); border: none; cursor: pointer; transition: all 0.4s cubic-bezier(0.4,0,0.2,1); }
.hero-dot:hover { background: rgba(255,255,255,0.5); }
.hero-dot-active { width: 32px; border-radius: 4px; background: white; }
.hero-scroll { position: absolute; bottom: 28px; left: 50%; transform: translateX(-50%); z-index: 10; width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.1); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.6); display: flex; align-items: center; justify-content: center; font-size: 12px; animation: float 3s ease-in-out infinite; transition: all 0.3s ease; text-decoration: none; }
.hero-scroll:hover { background: rgba(255,255,255,0.2); color: white; }
.hero-wave { position: absolute; bottom: -1px; left: 0; right: 0; z-index: 6; line-height: 0; }
.hero-wave svg { width: 100%; height: 80px; }
@media (max-width: 768px) { .hero-section { height: min(100svh, 700px); min-height: 500px; } .hero-orb-1 { width: 300px; height: 300px; } .hero-orb-2 { width: 200px; height: 200px; } .hero-orb-3 { display: none; } .hero-grid { background-size: 40px 40px; } .hero-wave svg { height: 50px; } }
</style>
