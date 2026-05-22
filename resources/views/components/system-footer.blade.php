@php
    $footerUser = auth()->user();
    $showMinimalGuestFooter = request()->routeIs('login') || request()->routeIs('register');
@endphp

<footer class="system-footer-wrap px-4 pb-4 pt-3">
    <div class="system-footer-shell mx-auto max-w-7xl">
        @guest
            @unless($showMinimalGuestFooter)
            <div class="system-footer-grid">
                <div class="system-footer-brand">
                    <h3 class="system-footer-heading">Local Sponsorship Portal</h3>
                    <p class="system-footer-copy">
                        Serving Child and Youths through church sponsorship in Jesus' name. Local Sponsorship Portal supports
                        internal Child and Youth sponsorship where a Child and Youth is supported by a church, ministry, or
                        individual sponsor, with the goal of strengthening care, dignity, and coordinated follow-up across
                        Kasulu &amp; Kigoma Northern Clusters.
                    </p>
                </div>

                <div>
                    <h4 class="system-footer-column-title">Quick Links</h4>
                    <div class="system-footer-links">
                        <a href="{{ url('/') }}" class="system-footer-link">Home</a>
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}" class="system-footer-link {{ request()->routeIs('register') ? 'active' : '' }}">Register</a>
                        @endif
                        @if(Route::has('login'))
                            <a href="{{ route('login') }}" class="system-footer-link {{ request()->routeIs('login') ? 'active' : '' }}">Login</a>
                        @endif
                    </div>
                </div>

                <div>
                    <h4 class="system-footer-column-title">Contact Info</h4>
                    <div class="system-footer-contact-list">
                        <div class="system-footer-contact-item">
                            <i class="bi bi-buildings-fill"></i>
                            <span>Local Sponsorship Management System</span>
                        </div>
                        <div class="system-footer-contact-item">
                            <i class="bi bi-envelope-fill"></i>
                            <span>info@localsponsorshipportal.org</span>
                        </div>
                        <div class="system-footer-contact-item">
                            <i class="bi bi-telephone-fill"></i>
                            <span>
                                +255 623 722 507 / Emmanuel Russota
                                <br>
                                ERussota@tz.ci.org
                            </span>
                        </div>
                        <div class="system-footer-contact-item">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>Kasulu &amp; Kigoma Northern Clusters, Tanzania</span>
                        </div>
                    </div>
                </div>
            </div>
            @endunless
        @endguest

        <div class="system-footer-bottom">
            <div class="system-footer-marquee">
                <div class="system-footer-marquee-track">
                    <span>Developed and maintained by Idriss ICT Services, Copyright &copy; 2026 Local Sponsorship Portal. All rights reserved.</span>
                    <span>Developed and maintained by Idriss ICT Services, Copyright &copy; 2026 Local Sponsorship Portal. All rights reserved.</span>
                </div>
            </div>

            @guest
                @unless($showMinimalGuestFooter)
                <div class="system-footer-policy-links">
                    <a href="#" onclick="return false;">Privacy Statement</a>
                    <a href="#" onclick="return false;">Terms of Use</a>
                </div>
                @endunless
            @endguest
        </div>
    </div>

    <button type="button" id="backToTopButton" class="back-to-top-button" aria-label="Back to top">
        <i class="bi bi-arrow-up"></i>
    </button>
</footer>

<script>
    (() => {
        const button = document.getElementById('backToTopButton');

        if (!button || button.dataset.bound === 'true') {
            return;
        }

        button.dataset.bound = 'true';

        const syncButton = () => {
            button.classList.toggle('is-visible', window.scrollY > 320);
        };

        button.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        window.addEventListener('scroll', syncButton, { passive: true });
        syncButton();
    })();
</script>
