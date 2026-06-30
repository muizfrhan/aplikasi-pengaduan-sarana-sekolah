/**
 * ============================================================
 * Main JavaScript - Aplikasi Pengaduan Sarana Sekolah
 * UKK SMK RPL Paket 3 Tahun 2026
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', function() {

    // ============================================================
    // Loading Screen
    // ============================================================
    const loadingScreen = document.getElementById('loading-screen');
    if (loadingScreen) {
        window.addEventListener('load', function() {
            setTimeout(function() {
                loadingScreen.classList.add('hidden');
            }, 500);
        });
        // Fallback: hide after 3 seconds if load event doesn't fire
        setTimeout(function() {
            if (!loadingScreen.classList.contains('hidden')) {
                loadingScreen.classList.add('hidden');
            }
        }, 3000);
    }

    // ============================================================
    // Back to Top
    // ============================================================
    const backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTop.style.display = 'flex';
            } else {
                backToTop.style.display = 'none';
            }
        });
        backToTop.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ============================================================
    // Sidebar Toggle (Mobile)
    // ============================================================
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('open');
        if (sidebarOverlay) sidebarOverlay.classList.add('show');
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (sidebarOverlay) sidebarOverlay.classList.remove('show');
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', openSidebar);
    }
    if (sidebarClose) {
        sidebarClose.addEventListener('click', closeSidebar);
    }
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    // ============================================================
    // Analog Clock
    // ============================================================
    const hourHand = document.getElementById('hourHand');
    if (hourHand) {
        const minuteHand = document.getElementById('minuteHand');
        const secondHand = document.getElementById('secondHand');
        const clockDay = document.getElementById('clockDay');
        const clockDate = document.getElementById('clockDate');
        const clockDigital = document.getElementById('clockDigital');

        // Generate tick marks
        const clockFace = document.querySelector('.analog-clock .clock-face');
        if (clockFace) {
            for (let i = 0; i < 60; i++) {
                const tick = document.createElement('div');
                tick.className = i % 5 === 0 ? 'clock-tick major' : 'clock-tick';
                tick.style.transform = `rotate(${i * 6}deg)`;
                clockFace.appendChild(tick);
            }
        }

        function updateClock() {
            const now = new Date();
            const h = now.getHours();
            const m = now.getMinutes();
            const s = now.getSeconds();
            const ms = now.getMilliseconds();

            const hDeg = (h % 12) * 30 + m * 0.5;
            const mDeg = m * 6 + s * 0.1;
            const sDeg = s * 6 + ms * 0.006;

            hourHand.style.transform = `rotate(${hDeg}deg)`;
            minuteHand.style.transform = `rotate(${mDeg}deg)`;
            secondHand.style.transform = `rotate(${sDeg}deg)`;

            const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            clockDay.textContent = days[now.getDay()];
            clockDate.textContent = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;

            const hh = String(h).padStart(2, '0');
            const mm = String(m).padStart(2, '0');
            const ss = String(s).padStart(2, '0');
            clockDigital.textContent = `${hh} : ${mm} : ${ss} WIB`;
        }
        updateClock();
        setInterval(updateClock, 1000);
    }

    // ============================================================
    // Counter Animation
    // ============================================================
    const counters = document.querySelectorAll('.counter');
    if (counters.length > 0) {
        const counterObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.getAttribute('data-target'));
                    const duration = 2000;
                    const step = Math.max(1, Math.floor(target / (duration / 16)));
                    let current = 0;

                    function updateCounter() {
                        current += step;
                        if (current >= target) {
                            counter.textContent = target.toLocaleString();
                            return;
                        }
                        counter.textContent = current.toLocaleString();
                        requestAnimationFrame(updateCounter);
                    }

                    updateCounter();
                    counterObserver.unobserve(counter);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(function(counter) {
            counterObserver.observe(counter);
        });
    }

    // ============================================================
    // Dark Mode Toggle
    // ============================================================
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        // Check saved preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
            darkModeToggle.checked = true;
        }

        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
                localStorage.setItem('theme', 'light');
            }
        });
    }

    // ============================================================
    // Delete Confirmation
    // ============================================================
    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            Swal.fire({
                title: 'Hapus Data?',
                text: 'Data yang dihapus tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                allowOutsideClick: false
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });

    // ============================================================
    // Ripple Effect
    // ============================================================
    document.querySelectorAll('.ripple').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const ripple = document.createElement('span');
            ripple.className = 'ripple-effect';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            this.appendChild(ripple);
            setTimeout(function() {
                ripple.remove();
            }, 600);
        });
    });

    // ============================================================
    // Image Preview (Click to fullsize)
    // ============================================================
    function previewImage(src) {
        Swal.fire({
            imageUrl: src,
            imageAlt: 'Preview',
            showConfirmButton: false,
            showCloseButton: true,
            background: 'transparent',
            width: 'auto'
        });
    }
    window.previewImage = previewImage;

    // ============================================================
    // Auto-hide Alerts
    // ============================================================
    setTimeout(function() {
        document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // ============================================================
    // Navbar & Scroll Spy (IntersectionObserver)
    // ============================================================
    const navbarLanding = document.querySelector('.navbar-landing');
    const navHeight = navbarLanding ? navbarLanding.offsetHeight : 70;

    // Smooth scroll with navbar offset
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const targetPos = target.getBoundingClientRect().top + window.scrollY - navHeight - 8;
                    window.scrollTo({ top: targetPos, behavior: 'smooth' });
                }
            }
        });
    });

    // IntersectionObserver-based scroll spy
    const spySections = document.querySelectorAll('section[id]');
    const spyLinks = document.querySelectorAll('.navbar-landing .nav-link');
    const menuItems = document.querySelectorAll('.menu-item[data-nav]');

    if (spySections.length && spyLinks.length) {
        const sectionObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    // Update desktop nav
                    spyLinks.forEach(function(link) {
                        link.classList.remove('active');
                        link.removeAttribute('aria-current');
                        if (link.getAttribute('href') === '#' + id) {
                            link.classList.add('active');
                            link.setAttribute('aria-current', 'page');
                        }
                    });
                    // Update mobile menu
                    menuItems.forEach(function(item) {
                        item.classList.remove('active');
                        if (item.getAttribute('href') === '#' + id) {
                            item.classList.add('active');
                        }
                    });
                }
            });
        }, {
            rootMargin: '-' + (navHeight + 8) + 'px 0px -40% 0px',
            threshold: 0.1
        });

        spySections.forEach(function(section) {
            sectionObserver.observe(section);
        });
    }

    // Shadow on scroll
    if (navbarLanding) {
        'scroll touchmove'.split(' ').forEach(function(evt) {
            window.addEventListener(evt, function() {
                navbarLanding.classList.toggle('scrolled', window.scrollY > 50);
            }, { passive: true });
        });
    }

    // ============================================================
    // Premium Mobile Menu
    // ============================================================
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const premiumMenu = document.getElementById('premiumMenu');
    const premiumOverlay = document.getElementById('premiumOverlay');
    const premiumPanel = document.getElementById('premiumPanel');

    if (hamburgerBtn && premiumMenu) {
        let isMenuOpen = false;
        let touchStartX = 0;
        let touchEndX = 0;

        function openMenu() {
            isMenuOpen = true;
            premiumMenu.classList.add('active');
            hamburgerBtn.classList.add('active');
            hamburgerBtn.setAttribute('aria-expanded', 'true');
            premiumMenu.setAttribute('aria-hidden', 'false');
            document.body.classList.add('menu-open');
            // Focus trap - focus first menu item
            const firstItem = premiumPanel.querySelector('.menu-item');
            if (firstItem) setTimeout(function() { firstItem.focus(); }, 400);
        }

        function closeMenu() {
            isMenuOpen = false;
            premiumMenu.classList.remove('active');
            hamburgerBtn.classList.remove('active');
            hamburgerBtn.setAttribute('aria-expanded', 'false');
            premiumMenu.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('menu-open');
            // Return focus to hamburger
            setTimeout(function() { hamburgerBtn.focus(); }, 100);
        }

        // Toggle on hamburger click
        hamburgerBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (isMenuOpen) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        // Close on overlay click
        if (premiumOverlay) {
            premiumOverlay.addEventListener('click', closeMenu);
        }

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isMenuOpen) {
                closeMenu();
            }
        });

        // Close on nav link click (smooth scroll handled elsewhere)
        premiumMenu.querySelectorAll('[data-nav]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                // Close menu after a small delay to let scroll start
                setTimeout(closeMenu, 150);
            });
        });

        // Swipe gesture: swipe right to close (swipe on overlay/panel)
        premiumMenu.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        premiumMenu.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            const swipeDistance = touchStartX - touchEndX;
            // Swipe left (negative) - close menu (panel slides right)
            // Swipe right (positive) - on the panel: close if swiped enough
            const target = e.target;
            const isOnPanel = target.closest('.premium-menu-panel');
            if (isOnPanel && swipeDistance < -60) {
                // Swiped right on panel - close
                closeMenu();
            } else if (!isOnPanel && swipeDistance > 60) {
                // Swiped left on overlay - also close
                closeMenu();
            }
        }, { passive: true });

        // Ripple effect on menu items
        premiumMenu.querySelectorAll('.menu-item').forEach(function(item) {
            item.addEventListener('click', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const ripple = document.createElement('span');
                ripple.className = 'ripple-effect';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                this.appendChild(ripple);
                setTimeout(function() {
                    ripple.remove();
                }, 600);
            });
        });

    }

});
