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

        <section id="intro" class="relative -mt-1 bg-white pb-10 md:pb-14">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="intro-panel reveal" :class="{'is-visible': vis.intro}">
                    <div class="intro-copy">
                        <div class="section-label">Tugas Pokok dan Fungsi</div>
                        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight mb-4">Mengawal penjaminan dan peningkatan mutu pendidikan di NTB.</h2>
                        <p>BPMP Provinsi NTB melaksanakan penjaminan dan peningkatan mutu pendidikan melalui pemetaan mutu, pendampingan pemerintah daerah dan satuan pendidikan, supervisi, serta pengelolaan data dan kemitraan.</p>
                    </div>
                    <div class="intro-points">
                        <div v-for="item in advantages" :key="item.title" class="intro-point">
                            <span><i :class="item.icon"></i></span>
                            <div><strong>{{ item.title }}</strong><small>{{ item.text }}</small></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="stats" class="py-14 md:py-20 bg-white relative overflow-hidden">
            <div class="parallax-decoration stats-glow" :style="sectionParallaxStyle(0.045, 90)"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="stats-panel">
                    <div v-for="(stat, idx) in statsData" :key="stat.label" class="stat-item reveal" :class="[`delay-${idx+1}`, {'is-visible': vis.stats}]">
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
            <div class="parallax-decoration kinerja-glow" :style="sectionParallaxStyle(-0.035, 120)"></div>
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
            <div class="parallax-decoration services-glow-a" :style="sectionParallaxStyle(0.04, 80)"></div>
            <div class="parallax-decoration services-glow-b" :style="sectionParallaxStyle(-0.055, 160)"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="text-center mb-16 reveal" :class="{'is-visible': vis.layanan}">
                    <div class="section-label justify-center">Layanan</div>
                    <h2 class="section-title">Layanan BPMP Provinsi NTB</h2>
                    <p class="section-subtitle mx-auto">Berbagai layanan penjaminan mutu pendidikan untuk masyarakat NTB</p>
                </div>
                <div class="max-w-5xl mx-auto mb-16 reveal delay-1" :class="{'is-visible': vis.layanan}">
                    <article class="service-pledge">
                        <div class="pledge-pattern"></div>
                        <div class="pledge-watermark">M</div>
                        <aside class="pledge-seal">
                            <span>Komitmen</span>
                            <div><i class="fas fa-shield-check"></i></div>
                            <strong>Pelayanan</strong>
                            <small>BPMP NTB</small>
                        </aside>
                        <div class="pledge-copy">
                            <div class="pledge-eyebrow"><span></span>Deklarasi Layanan Publik <b>01</b></div>
                            <h3>MAKLUMAT<br><em>PELAYANAN</em></h3>
                            <div class="pledge-line"></div>
                            <blockquote>“Dengan ini, kami sanggup menyelenggarakan pelayanan sesuai standar pelayanan yang telah ditetapkan. Apabila tidak menepati janji ini, kami siap menerima sanksi sesuai ketentuan peraturan perundang-undangan.”</blockquote>
                            <div class="pledge-values">
                                <span><i class="fas fa-scale-balanced"></i><b>Terstandar</b><small>Prosedur yang jelas</small></span>
                                <span><i class="fas fa-gem"></i><b>Bermutu</b><small>Layanan berkualitas</small></span>
                                <span><i class="fas fa-fingerprint"></i><b>Akuntabel</b><small>Dapat dipertanggungjawabkan</small></span>
                            </div>
                        </div>
                    </article>
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
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold mt-3 opacity-70 md:opacity-0 md:group-hover:opacity-100 transition-all duration-300" style="color:var(--color-primary)">Selengkapnya <i class="fas fa-arrow-right text-[9px]"></i></span>
                            </div>
                        </div>
                    </component>
                </div>
                <div v-if="layanan.length > 6" class="text-center mt-10 reveal delay-5" :class="{'is-visible': vis.layanan}">
                    <router-link to="/layanan" class="btn-outline py-3.5 px-8 text-sm">Lihat Semua Layanan <i class="fas fa-arrow-right ml-2 text-[10px]"></i></router-link>
                </div>
            </div>
        </section>

        <section id="process" class="py-24 bg-white relative overflow-hidden">
            <div class="parallax-decoration process-ring" :style="sectionParallaxStyle(0.035, 120)"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="text-center mb-14 reveal" :class="{'is-visible': vis.process}">
                    <div class="section-label justify-center">Alur Pelayanan</div>
                    <h2 class="section-title">Mudah, transparan, dan terukur.</h2>
                    <p class="section-subtitle mx-auto">Setiap permohonan layanan diproses melalui tahapan yang jelas agar pengguna mendapat kepastian.</p>
                </div>
                <div class="process-grid">
                    <article v-for="(item, idx) in processSteps" :key="item.title" class="process-card reveal" :class="[`delay-${idx + 1}`, {'is-visible': vis.process}]">
                        <span class="process-number">0{{ idx + 1 }}</span>
                        <div class="process-icon"><i :class="item.icon"></i></div>
                        <h3>{{ item.title }}</h3>
                        <p>{{ item.text }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section v-if="setting?.silamo_link" class="py-20 relative overflow-hidden surface-grid" data-silamo style="background-color:#EFF6FF">
            <div class="parallax-decoration silamo-glow" :style="sectionParallaxStyle(-0.04, 100)"></div>
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="silamo-panel reveal-scale" :class="{'is-visible': vis.silamo}">
                    <div class="silamo-qr">
                        <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='+encodeURIComponent(setting.silamo_link)" alt="QR Zoom SILAMO" loading="lazy" width="176" height="176">
                    </div>
                    <div class="silamo-copy">
                        <div class="section-label">Layanan Daring</div>
                        <h2>{{ setting.silamo_title || 'SILAMO (Sistem Layanan Melalui Online)' }}</h2>
                        <p>{{ setting.silamo_subtitle || 'Konsultasi langsung bersama BPMP Provinsi NTB tanpa harus datang ke kantor.' }}</p>
                        <div class="silamo-meta">
                            <span><i class="fas fa-calendar-check"></i>{{ setting.silamo_schedule || 'Senin s.d Jumat: 09.00 s.d. 11.00 WITA' }}</span>
                            <span><i class="fas fa-video"></i>ID {{ setting.silamo_meeting_id || '349 664 0348' }}</span>
                            <span><i class="fas fa-key"></i>{{ setting.silamo_password || 'ultbpmpntb' }}</span>
                        </div>
                        <a :href="setting.silamo_link" target="_blank" rel="noopener noreferrer" class="btn-primary">Buka SILAMO <i class="fas fa-arrow-up-right-from-square text-xs"></i></a>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-24 bg-white relative overflow-hidden" data-posts>
            <div class="parallax-decoration posts-glow" :style="sectionParallaxStyle(0.04, 120)"></div>
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
                <div v-else-if="lastPost.length >= 3" class="home-post-layout">
                    <router-link :to="`/post/${lastPost[0].jenis}/${lastPost[0].id}/${lastPost[0].slug}`" class="home-featured-post group reveal" :class="{'is-visible': vis.posts}">
                        <div class="home-featured-media">
                            <img v-if="lastPost[0].image_url" :src="lastPost[0].image_url" :alt="lastPost[0].title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy" @error="handleThumbError($event,lastPost[0])">
                            <div v-else class="w-full h-full flex items-center justify-center bg-gray-50"><i class="fas fa-newspaper text-5xl text-gray-200"></i></div>
                            <div class="home-post-shade"></div>
                            <div class="home-featured-content">
                                <span class="home-post-category">{{ lastPost[0].kategori }}</span>
                                <h3>{{ lastPost[0].title }}</h3>
                                <p>{{ lastPost[0].teaser }}</p>
                                <div class="home-post-meta"><span><i class="fas fa-calendar"></i>{{ formatDate(lastPost[0].tanggal) }}</span><span><i class="fas fa-user"></i>{{ lastPost[0].writer }}</span></div>
                            </div>
                        </div>
                    </router-link>
                    <div class="home-post-list">
                        <router-link v-for="(item, idx) in lastPost.slice(1, 4)" :key="item.id+item.jenis" :to="`/post/${item.jenis}/${item.id}/${item.slug}`" class="home-post-row group reveal" :class="[`delay-${idx+1}`, {'is-visible': vis.posts}]">
                            <div class="home-post-thumb">
                                <img v-if="item.image_url" :src="item.image_url" :alt="item.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" @error="handleThumbError($event,item)">
                                <div v-else class="w-full h-full flex items-center justify-center bg-gray-50"><i class="fas fa-newspaper text-2xl text-gray-200"></i></div>
                            </div>
                            <div class="home-post-row-copy">
                                <div><span>{{ item.kategori }}</span><time>{{ formatDate(item.tanggal) }}</time></div>
                                <h4>{{ item.title }}</h4>
                                <p>{{ item.teaser }}</p>
                                <small>Baca selengkapnya <i class="fas fa-arrow-right"></i></small>
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

        <section class="cta-section">
            <div class="cta-grid"></div>
            <div class="parallax-decoration cta-orb" :style="sectionParallaxStyle(-0.03, 80)"></div>
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative text-center reveal" :class="{'is-visible': vis.cta}">
                <span class="cta-kicker">Siap Melayani</span>
                <h2>Temukan layanan pendidikan yang Anda butuhkan.</h2>
                <p>Jelajahi seluruh layanan BPMP Provinsi NTB atau sampaikan kebutuhan Anda melalui kanal informasi resmi.</p>
                <div class="cta-actions">
                    <router-link to="/layanan" class="btn-primary">Lihat Semua Layanan <i class="fas fa-arrow-right text-xs"></i></router-link>
                    <router-link to="/ppid" class="btn-secondary">Informasi PPID</router-link>
                </div>
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
import PublicLayout from '@/layouts/PublicLayout.vue';
import { usePublicData } from '@/composables/usePublicData.js';

const { setting, beranda, layanan, visitorStats, fetchAll } = usePublicData();
const sliders = computed(() => beranda.value?.sliders || []);
const lastPost = computed(() => beranda.value?.lastPost || []);
const externalLinks = computed(() => beranda.value?.externalLinks || []);
const slide = ref(0); let timer = null;
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
const advantages = [
    { icon: 'fas fa-chart-area', title: 'Pemetaan Mutu', text: 'Memetakan capaian dan kebutuhan mutu pendidikan.' },
    { icon: 'fas fa-people-group', title: 'Pendampingan', text: 'Mendampingi pemerintah daerah dan satuan pendidikan.' },
    { icon: 'fas fa-database', title: 'Data dan Kemitraan', text: 'Mengelola data mutu serta membangun kolaborasi.' },
];
const processSteps = [
    { icon: 'fas fa-magnifying-glass', title: 'Pilih Layanan', text: 'Temukan layanan yang sesuai dengan kebutuhan Anda.' },
    { icon: 'fas fa-file-pen', title: 'Ajukan Permohonan', text: 'Lengkapi informasi atau dokumen yang dipersyaratkan.' },
    { icon: 'fas fa-gears', title: 'Proses Verifikasi', text: 'Tim kami memeriksa dan menindaklanjuti permohonan.' },
    { icon: 'fas fa-circle-check', title: 'Layanan Selesai', text: 'Hasil layanan disampaikan melalui kanal yang tersedia.' },
];

const statsData = computed(() => [
    { icon: 'fas fa-users', value: visitorStats.value?.totalVisitors || 0, label: 'Total Pengunjung' },
    { icon: 'fas fa-concierge-bell', value: layanan.value?.length || 0, label: 'Layanan Tersedia' },
    { icon: 'fas fa-star', value: parseFloat(setting.value?.ikm_score) || 0, label: 'Skor IKM' },
    { icon: 'fas fa-globe', value: visitorStats.value?.onlineVisitors || 0, label: 'Online Saat Ini' },
]);

const vis = reactive({ intro: false, stats: false, kinerja: false, layanan: false, process: false, silamo: false, posts: false, cta: false, links: false });
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
    const targets = { '#intro': 'intro', '#stats': 'stats', '#kinerja': 'kinerja', '#layanan': 'layanan', '#process': 'process', '[data-silamo]': 'silamo', '[data-posts]': 'posts', '.cta-section': 'cta', '[data-links]': 'links' };
    for (const [sel, key] of Object.entries(targets)) {
        const el = document.querySelector(sel); if (!el) { vis[key] = true; continue; }
        const obs = new IntersectionObserver(([entry]) => { if (entry.isIntersecting) { vis[key] = true; if (key === 'stats') animateCounters(); obs.disconnect(); } }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
        obs.observe(el); observers.push(obs);
    }
}

function onScroll() { scrollY.value = window.scrollY; }

function sectionParallaxStyle(speed, limit) {
    if (prefersReducedMotion || typeof window === 'undefined' || window.innerWidth < 768) return {};
    const movement = Math.max(-limit, Math.min(limit, scrollY.value * speed));
    return { transform: `translate3d(0, ${movement}px, 0)` };
}

onMounted(async () => {
    window.addEventListener('scroll', onScroll, { passive: true });
    await fetchAll();
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
.hero-content { position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: center; z-index: 5; max-width: 1280px; margin: 0 auto; padding: 32px 24px 96px; }
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
.parallax-decoration { position:absolute; pointer-events:none; will-change:transform; }
.stats-glow { top:-180px; left:50%; width:760px; height:420px; border-radius:50%; background:radial-gradient(circle, color-mix(in srgb, var(--color-primary) 18%, transparent), transparent 68%); filter:blur(40px); }
.kinerja-glow { right:-220px; bottom:-170px; width:560px; height:560px; border-radius:50%; background:radial-gradient(circle, color-mix(in srgb, var(--color-primary-light) 14%, transparent), transparent 70%); filter:blur(48px); }
.services-glow-a { right:-120px; top:120px; width:520px; height:520px; border-radius:50%; background:radial-gradient(circle, color-mix(in srgb, var(--color-primary-light) 22%, transparent), transparent 70%); filter:blur(54px); }
.services-glow-b { left:-150px; bottom:80px; width:440px; height:440px; border-radius:50%; background:radial-gradient(circle, color-mix(in srgb, var(--color-primary) 16%, transparent), transparent 70%); filter:blur(48px); }
.process-ring { right:-120px; top:60px; width:360px; height:360px; border:54px solid color-mix(in srgb, var(--color-primary) 7%, transparent); border-radius:50%; }
.silamo-glow { left:-120px; top:0; width:420px; height:420px; border-radius:50%; background:radial-gradient(circle, color-mix(in srgb, var(--color-primary) 14%, transparent), transparent 70%); filter:blur(58px); }
.posts-glow { right:-180px; bottom:-160px; width:520px; height:520px; border-radius:50%; background:radial-gradient(circle, color-mix(in srgb, var(--color-primary-light) 13%, transparent), transparent 70%); filter:blur(70px); }
.intro-panel { position:relative; display:grid; gap:32px; padding:32px; border:1px solid rgba(37,99,235,.1); border-radius:28px; background:rgba(255,255,255,.94); box-shadow:0 24px 70px rgba(30,64,175,.1); }
.intro-copy h2 { color:#263238; }
.intro-copy p { color:var(--color-text-secondary); line-height:1.8; max-width:620px; }
.intro-points { display:grid; gap:14px; }
.intro-point { display:flex; align-items:flex-start; gap:14px; padding:14px; border-radius:16px; background:#F8FAFC; border:1px solid #EEF2F7; }
.intro-point > span { display:grid; place-items:center; width:42px; height:42px; flex:0 0 42px; border-radius:13px; color:var(--color-primary); background:#EFF6FF; }
.intro-point strong,.intro-point small { display:block; }
.intro-point strong { font-size:14px; color:#334155; margin-bottom:3px; }
.intro-point small { color:#78909C; line-height:1.5; }
.service-pledge { position:relative; display:grid; overflow:hidden; min-height:440px; border-radius:30px; background:#173D9A; box-shadow:0 30px 80px rgba(30,64,175,.26); isolation:isolate; }
.pledge-pattern { position:absolute; inset:0; z-index:-2; opacity:.13; background-image:linear-gradient(rgba(255,255,255,.25) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.25) 1px,transparent 1px); background-size:42px 42px; mask-image:linear-gradient(90deg,transparent,black 35%,black); }
.pledge-watermark { position:absolute; right:-28px; bottom:-110px; z-index:-1; color:rgba(255,255,255,.045); font-size:360px; font-weight:900; line-height:1; transform:rotate(-8deg); }
.pledge-seal { position:relative; display:flex; align-items:center; justify-content:center; flex-direction:column; padding:34px 24px; color:#fff; background:#0F2F7F; border-bottom:1px solid rgba(255,255,255,.12); text-align:center; }
.pledge-seal::after { content:''; position:absolute; right:-1px; top:12%; bottom:12%; width:1px; background:linear-gradient(transparent,rgba(255,255,255,.3),transparent); }
.pledge-seal>span { margin-bottom:24px; color:rgba(255,255,255,.5); font-size:10px; font-weight:700; letter-spacing:.18em; text-transform:uppercase; }
.pledge-seal>div { display:grid; place-items:center; width:108px; height:108px; margin-bottom:20px; border:1px solid rgba(255,255,255,.22); border-radius:50%; background:rgba(255,255,255,.08); box-shadow:inset 0 0 0 8px rgba(255,255,255,.035),0 14px 34px rgba(5,20,60,.25); font-size:40px; }
.pledge-seal strong { font-size:16px; letter-spacing:.08em; text-transform:uppercase; }
.pledge-seal small { margin-top:5px; color:rgba(255,255,255,.45); font-size:10px; letter-spacing:.12em; }
.pledge-copy { position:relative; padding:38px 28px 34px; color:#fff; }
.pledge-eyebrow { display:flex; align-items:center; gap:10px; margin-bottom:22px; color:rgba(255,255,255,.5); font-size:10px; font-weight:700; letter-spacing:.15em; text-transform:uppercase; }
.pledge-eyebrow>span { width:28px; height:2px; background:#60A5FA; }
.pledge-eyebrow b { margin-left:auto; color:rgba(255,255,255,.24); font-size:34px; line-height:1; }
.pledge-copy h3 { color:#fff; font-size:clamp(2.45rem,6vw,4.8rem); font-weight:900; line-height:.88; letter-spacing:-.055em; }
.pledge-copy h3 em { color:#93C5FD; font-style:normal; }
.pledge-line { width:72px; height:4px; margin:24px 0; border-radius:99px; background:#60A5FA; }
.pledge-copy blockquote { max-width:680px; color:rgba(255,255,255,.8); font-family:Georgia,'Times New Roman',serif; font-size:clamp(1rem,2vw,1.18rem); font-style:italic; line-height:1.85; }
.pledge-values { display:grid; gap:10px; margin-top:30px; }
.pledge-values>span { display:grid; grid-template-columns:36px 1fr; align-items:center; padding:11px 13px; border:1px solid rgba(255,255,255,.11); border-radius:14px; background:rgba(255,255,255,.06); }
.pledge-values i { grid-row:1/3; color:#93C5FD; font-size:15px; }
.pledge-values b,.pledge-values small { display:block; }
.pledge-values b { font-size:11px; letter-spacing:.04em; text-transform:uppercase; }
.pledge-values small { margin-top:2px; color:rgba(255,255,255,.43); font-size:9px; }
.stats-panel { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); padding:10px; border:1px solid rgba(37,99,235,.08); border-radius:24px; background:#fff; box-shadow:0 18px 50px rgba(15,23,42,.07); }
.stat-item { padding:24px 12px; text-align:center; }
.process-grid { display:grid; gap:18px; }
.process-card { position:relative; padding:28px; border:1px solid #E2E8F0; border-radius:22px; background:#fff; box-shadow:0 12px 32px rgba(15,23,42,.05); }
.process-number { position:absolute; right:20px; top:16px; color:#E2E8F0; font-size:34px; font-weight:900; }
.process-icon { display:grid; place-items:center; width:52px; height:52px; margin-bottom:20px; border-radius:16px; color:#fff; background:var(--color-primary); box-shadow:0 10px 24px color-mix(in srgb,var(--color-primary) 24%,transparent); }
.process-card h3 { color:#334155; font-size:16px; margin-bottom:8px; }
.process-card p { color:#78909C; font-size:13px; line-height:1.7; }
.silamo-panel { display:grid; gap:32px; align-items:center; padding:32px; border:1px solid rgba(37,99,235,.12); border-radius:28px; background:rgba(255,255,255,.9); box-shadow:0 24px 70px rgba(30,64,175,.09); backdrop-filter:blur(16px); }
.silamo-qr { width:max-content; padding:14px; margin:auto; border-radius:22px; background:#fff; box-shadow:0 12px 30px rgba(15,23,42,.1); }
.silamo-qr img { width:176px; height:176px; border-radius:12px; }
.silamo-copy h2 { color:#263238; font-size:clamp(1.5rem,3vw,2.2rem); margin-bottom:12px; }
.silamo-copy > p { color:#78909C; line-height:1.75; margin-bottom:22px; }
.silamo-meta { display:flex; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
.silamo-meta span { display:inline-flex; align-items:center; gap:8px; padding:9px 12px; border-radius:12px; color:#546E7A; background:#F8FAFC; border:1px solid #E2E8F0; font-size:12px; }
.silamo-meta i { color:var(--color-primary); }
.cta-section { position:relative; overflow:hidden; padding:88px 0; background:#1E40AF; }
.cta-grid { position:absolute; inset:0; opacity:.08; background-image:linear-gradient(rgba(255,255,255,.2) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.2) 1px,transparent 1px); background-size:48px 48px; }
.cta-orb { right:5%; top:-140px; width:420px; height:420px; border-radius:50%; background:radial-gradient(circle,rgba(96,165,250,.45),transparent 70%); filter:blur(40px); }
.cta-kicker { display:inline-flex; margin-bottom:14px; padding:7px 13px; border:1px solid rgba(255,255,255,.16); border-radius:999px; color:rgba(255,255,255,.7); font-size:11px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; }
.cta-section h2 { color:#fff; font-size:clamp(2rem,4vw,3.4rem); margin-bottom:16px; }
.cta-section p { max-width:650px; margin:0 auto 30px; color:rgba(255,255,255,.65); font-size:17px; line-height:1.75; }
.cta-actions { display:flex; justify-content:center; flex-wrap:wrap; gap:14px; }
.home-post-layout { display:grid; gap:24px; }
.home-featured-post,.home-post-row { overflow:hidden; border:1px solid #E2E8F0; border-radius:24px; background:#fff; box-shadow:0 14px 42px rgba(15,23,42,.06); transition:transform .3s ease,box-shadow .3s ease,border-color .3s ease; }
.home-featured-post:hover,.home-post-row:hover { transform:translateY(-5px); border-color:rgba(37,99,235,.2); box-shadow:0 24px 60px rgba(15,23,42,.11); }
.home-featured-media { position:relative; height:100%; min-height:440px; overflow:hidden; }
.home-post-shade { position:absolute; inset:0; background:linear-gradient(180deg,transparent 22%,rgba(15,23,42,.9) 100%); }
.home-featured-content { position:absolute; left:0; right:0; bottom:0; z-index:2; padding:32px; color:#fff; }
.home-post-category { display:inline-flex; margin-bottom:14px; padding:6px 11px; border:1px solid rgba(255,255,255,.18); border-radius:999px; background:rgba(255,255,255,.12); font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; backdrop-filter:blur(8px); }
.home-featured-content h3 { display:-webkit-box; overflow:hidden; margin-bottom:10px; color:#fff; font-size:clamp(1.55rem,3vw,2.35rem); line-height:1.25; -webkit-box-orient:vertical; -webkit-line-clamp:2; }
.home-featured-content p { display:-webkit-box; overflow:hidden; max-width:700px; margin-bottom:16px; color:rgba(255,255,255,.66); font-size:13px; line-height:1.7; -webkit-box-orient:vertical; -webkit-line-clamp:2; }
.home-post-meta { display:flex; flex-wrap:wrap; gap:16px; color:rgba(255,255,255,.6); font-size:10px; }
.home-post-meta span { display:inline-flex; align-items:center; gap:6px; }
.home-post-list { display:grid; gap:16px; }
.home-post-row { display:grid; grid-template-columns:130px minmax(0,1fr); padding:14px; }
.home-post-thumb { min-height:130px; overflow:hidden; border-radius:16px; background:#F1F5F9; }
.home-post-row-copy { min-width:0; padding:6px 8px 4px 18px; }
.home-post-row-copy>div { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px; }
.home-post-row-copy>div span { color:var(--color-primary); font-size:9px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; }
.home-post-row-copy time { color:#94A3B8; font-size:9px; }
.home-post-row-copy h4 { display:-webkit-box; overflow:hidden; margin-bottom:7px; color:#334155; font-size:14px; line-height:1.45; -webkit-box-orient:vertical; -webkit-line-clamp:2; }
.home-post-row-copy p { display:-webkit-box; overflow:hidden; color:#78909C; font-size:11px; line-height:1.55; -webkit-box-orient:vertical; -webkit-line-clamp:2; }
.home-post-row-copy small { display:inline-flex; align-items:center; gap:6px; margin-top:9px; color:var(--color-primary); font-size:9px; font-weight:700; }
.group:hover .home-post-row-copy small { gap:9px; }
@media (min-width:768px) {
    .intro-panel { grid-template-columns:1.15fr .85fr; padding:42px; align-items:center; }
    .stats-panel { grid-template-columns:repeat(4,minmax(0,1fr)); }
    .stat-item + .stat-item { border-left:1px solid #EEF2F7; }
    .process-grid { grid-template-columns:repeat(4,minmax(0,1fr)); }
    .silamo-panel { grid-template-columns:auto 1fr; padding:44px; }
    .service-pledge { grid-template-columns:230px minmax(0,1fr); }
    .pledge-seal { border-right:1px solid rgba(255,255,255,.1); border-bottom:0; }
    .pledge-copy { padding:48px 52px 42px; }
    .pledge-values { grid-template-columns:repeat(3,minmax(0,1fr)); }
}
@media (min-width:1024px) {
    .home-post-layout { grid-template-columns:minmax(0,1.18fr) minmax(360px,.82fr); }
}
@media (max-width: 768px) {
    .hero-section { height:min(100svh,760px); min-height:600px; }
    .hero-content { padding:72px 20px 110px; justify-content:center; }
    .hero-badge { max-width:calc(100vw - 40px); font-size:9px; line-height:1.45; letter-spacing:.08em; }
    .hero-desc { font-size:15px; margin-bottom:28px; }
    .hero-cta { width:100%; }
    .hero-cta > * { flex:1 1 100%; min-height:48px; }
    .hero-nav { display:none; }
    .hero-dots { bottom:76px; }
    .hero-scroll { display:none; }
    .hero-orb-1 { width:300px; height:300px; }
    .hero-orb-2 { width:200px; height:200px; }
    .hero-orb-3 { display:none; }
    .hero-grid { background-size:40px 40px; }
    .hero-wave svg { height:50px; }
    .intro-panel { padding:26px 22px; }
    .service-pledge { min-height:0; }
    .pledge-seal { padding:28px 20px; }
    .pledge-seal>span { margin-bottom:14px; }
    .pledge-seal>div { width:82px; height:82px; margin-bottom:14px; font-size:29px; }
    .pledge-copy { padding:30px 22px; }
    .pledge-eyebrow b { font-size:26px; }
    .pledge-copy h3 { font-size:2.65rem; }
    .stats-panel { padding:6px; }
    .stat-item { padding:20px 8px; }
    .process-card { padding:24px; }
    .silamo-panel { padding:26px 20px; }
    .silamo-copy { text-align:center; }
    .silamo-meta { justify-content:center; }
    .silamo-copy .btn-primary { width:100%; }
    .cta-section { padding:72px 0; }
    .home-featured-media { min-height:380px; }
    .home-featured-content { padding:24px; }
    .home-post-row { grid-template-columns:105px minmax(0,1fr); }
    .home-post-thumb { min-height:118px; }
    .home-post-row-copy { padding-left:13px; }
    .home-post-row-copy p { display:none; }
}
@media (prefers-reduced-motion: reduce) {
    .parallax-decoration { transform:none !important; }
}
</style>
