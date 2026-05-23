<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $siteFaviconPath = public_path('images/site-favicon.png');
        $siteFaviconUrl = file_exists($siteFaviconPath)
            ? asset('images/site-favicon.png') . '?v=' . filemtime($siteFaviconPath)
            : asset('images/compassion-mark.png');
    @endphp
    <title>Local Sponsorship Portal | Kasulu & Kigoma Northern Clusters</title>

    <link rel="icon" type="image/png" href="{{ $siteFaviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $siteFaviconUrl }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background: #f8fafc;
            color: #0f172a;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }

        header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(191, 219, 254, 0.85);
        }

        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 80px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            line-height: 1.1;
            text-decoration: none;
        }

        .logo-badge {
            border-radius: 999px;
            background: #0f5eb8;
            padding: 0.55rem;
            box-shadow: 0 14px 26px rgba(37, 99, 235, 0.2);
        }

        .logo-badge img {
            display: block;
            width: 64px;
            height: 64px;
            border-radius: 999px;
            object-fit: cover;
            transition: opacity 0.35s ease;
        }

        .logo-copy {
            display: flex;
            flex-direction: column;
        }

        .logo-main {
            font-size: 1.2rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.2px;
        }

        .logo-sub {
            font-size: 0.72rem;
            color: #2563eb;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.8rem;
        }

        .nav-links a {
            color: #475569;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: 0.3s ease;
        }

        .nav-links a:hover {
            color: #1d4ed8;
        }

        .auth-buttons {
            display: flex;
            gap: 0.8rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.74rem 1.2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.94rem;
            transition: 0.3s ease;
            border: 1px solid transparent;
        }

        .btn-outline {
            color: #1d4ed8;
            border-color: #bfdbfe;
            background: #ffffff;
        }

        .btn-outline:hover {
            background: #eff6ff;
            color: #0f172a;
        }

        .btn-solid {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
        }

        .btn-solid:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 28px rgba(37, 99, 235, 0.28);
        }

        .hero {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top left, rgba(191, 219, 254, 0.25), transparent 24%),
                radial-gradient(circle at bottom right, rgba(147, 197, 253, 0.18), transparent 20%),
                linear-gradient(135deg, #0f5eb8 0%, #1d4ed8 45%, #2563eb 100%);
            color: white;
            padding: 88px 0 105px;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 3rem;
            align-items: center;
        }

        .eyebrow {
            display: inline-block;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            color: #dbeafe;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 1.3rem;
            letter-spacing: 0.04em;
        }

        .hero h1 {
            font-size: 3.45rem;
            line-height: 1.08;
            font-weight: 800;
            margin-bottom: 1.2rem;
        }

        .hero p {
            font-size: 1.08rem;
            color: #dbeafe;
            max-width: 760px;
            margin-bottom: 2rem;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.8rem;
        }

        .btn-light {
            background: #ffffff;
            color: #0f5eb8;
        }

        .btn-light:hover {
            transform: translateY(-2px);
            background: #eff6ff;
        }

        .btn-dark {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.14);
            color: #ffffff;
        }

        .btn-dark:hover {
            background: rgba(255,255,255,0.16);
            transform: translateY(-2px);
        }

        .hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.9rem;
        }

        .meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.10);
            color: #e2e8f0;
            padding: 0.72rem 1rem;
            border-radius: 999px;
            font-size: 0.9rem;
        }

        .hero-card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 24px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.28);
        }

        .hero-card h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .hero-card p {
            margin-bottom: 1rem;
            font-size: 0.98rem;
            color: #e2e8f0;
        }

        .hero-points {
            list-style: none;
            margin-top: 1rem;
        }

        .hero-points li {
            display: flex;
            align-items: flex-start;
            gap: 0.8rem;
            color: #f8fafc;
            margin-bottom: 0.95rem;
            font-size: 0.96rem;
        }

        .hero-points i {
            color: #bfdbfe;
            margin-top: 0.2rem;
        }

        .mission {
            padding: 90px 0;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .section-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            align-items: start;
        }

        .section-tag {
            color: #2563eb;
            font-size: 0.86rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 1rem;
            display: inline-block;
        }

        .section-title {
            font-size: 2.45rem;
            font-weight: 800;
            line-height: 1.2;
            color: #0f172a;
            margin-bottom: 1rem;
        }

        .section-text {
            color: #475569;
            font-size: 1.03rem;
            line-height: 1.9;
            margin-bottom: 1rem;
        }

        .highlight-card {
            background: #ffffff;
            border: 1px solid #dbeafe;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }

        .clusters {
            padding: 84px 0;
            background: #eff6ff;
        }

        .clusters-header {
            text-align: center;
            max-width: 820px;
            margin: 0 auto 3rem;
        }

        .clusters-header h2 {
            font-size: 2.4rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 1rem;
        }

        .clusters-header p {
            color: #64748b;
            font-size: 1.05rem;
            line-height: 1.8;
        }

        .clusters-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.2rem;
        }

        .cluster-card {
            background: #ffffff;
            border-radius: 22px;
            padding: 1.8rem;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
            border: 1px solid #dbeafe;
            transition: 0.3s ease;
        }

        .cluster-card:hover {
            transform: translateY(-6px);
        }

        .cluster-icon {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .cluster-card h3 {
            font-size: 1.15rem;
            color: #0f172a;
            margin-bottom: 0.75rem;
        }

        .cluster-card p {
            color: #64748b;
            line-height: 1.75;
            font-size: 0.97rem;
        }

        .features {
            padding: 85px 0;
            background: #ffffff;
        }

        .features-header {
            text-align: center;
            max-width: 780px;
            margin: 0 auto 3rem;
        }

        .features-header h2 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 1rem;
        }

        .features-header p {
            color: #64748b;
            font-size: 1.05rem;
            line-height: 1.8;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.2rem;
        }

        .feature-card {
            background: #f8fafc;
            border-radius: 22px;
            padding: 1.8rem;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.04);
            border: 1px solid #dbeafe;
            transition: 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-6px);
        }

        .feature-icon {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            font-size: 1.2rem;
            color: #0f172a;
            margin-bottom: 0.75rem;
        }

        .feature-card p {
            color: #64748b;
            line-height: 1.75;
            font-size: 0.97rem;
        }

        .footer {
            background: #0f5eb8;
            color: white;
            padding: 70px 0 24px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer h3 {
            font-size: 1.45rem;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .footer h4 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .footer p,
        .footer li,
        .footer span {
            color: #dbeafe;
            line-height: 1.9;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: #dbeafe;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .footer-links a:hover {
            color: #ffffff;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 0.8rem;
            margin-bottom: 0.9rem;
        }

        .contact-item i {
            color: #bfdbfe;
            margin-top: 0.35rem;
            min-width: 16px;
        }

        .footer-bottom {
            padding-top: 1.3rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            text-align: center;
            color: #dbeafe;
            font-size: 0.92rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.85rem;
            flex-wrap: wrap;
        }

        .footer-bottom a {
            color: #dbeafe;
            text-decoration: none;
        }

        .footer-bottom a:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        @media (max-width: 1024px) {
            .hero-grid,
            .section-grid,
            .footer-content {
                grid-template-columns: 1fr;
            }

            .clusters-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .features-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .hero h1 {
                font-size: 2.7rem;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .auth-buttons {
                gap: 0.5rem;
            }

            .btn {
                padding: 0.65rem 0.95rem;
                font-size: 0.88rem;
            }

            .hero {
                padding: 70px 0 85px;
            }

            .hero h1,
            .section-title,
            .clusters-header h2,
            .features-header h2 {
                font-size: 2rem;
            }

            .clusters-grid,
            .features-grid {
                grid-template-columns: 1fr;
            }

            .hero-actions {
                flex-direction: column;
            }

            .hero-actions .btn {
                width: 100%;
            }

            .hero-meta {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    @php
        $welcomeLogos = [
            asset('images/welcome-rotating-logo-1.jpeg'),
            asset('images/welcome-rotating-logo-2.jpeg'),
        ];
    @endphp
    <header>
        <nav class="container">
            <a href="#home" class="logo">
                <div class="logo-badge">
                    <img
                        id="welcome-rotating-logo"
                        src="{{ $welcomeLogos[0] }}"
                        alt="Local Sponsorship Portal Logo"
                        data-logos='@json($welcomeLogos)'>
                </div>
                <div class="logo-copy">
                    <span class="logo-main">Local Sponsorship Portal</span>
                    <span class="logo-sub">Kasulu & Kigoma Northern Clusters</span>
                </div>
            </a>

            <div class="nav-links">
                <a href="#home">Home</a>
                <a href="#mission">Mission</a>
                <a href="#features">Features</a>
                <a href="#contact">Contact</a>
            </div>

            <div class="auth-buttons">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-solid">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-solid">Register</a>
                        @endif
                    @endauth
                @endif
            </div>
        </nav>
    </header>

    <section id="home" class="hero">
        <div class="container">
            <div class="hero-grid">
                <div>
                    <span class="eyebrow">Local Sponsorship Management</span>

                    <h1>Serving Child and Youths through church sponsorship in Jesus' name.</h1>

                    <p>
                        Local Sponsorship Portal supports internal Child and Youth sponsorship where a Child and Youth is supported by a church, ministry, or individual sponsor, with the goal of strengthening care, dignity, and coordinated follow-up across Kasulu & Kigoma Northern Clusters.
                    </p>

                    <div class="hero-actions">
                        <a href="#mission" class="btn btn-light">Learn More</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-dark">Join the Portal</a>
                        @endif
                    </div>

                    <div class="hero-meta">
                        <div class="meta-pill">
                            <i class="fas fa-location-dot"></i>
                            <span>Kasulu & Kigoma Northern Clusters</span>
                        </div>
                        <div class="meta-pill">
                            <i class="fas fa-hand-holding-heart"></i>
                            <span>Church & Individual Sponsorship</span>
                        </div>
                    </div>
                </div>

                <div class="hero-card">
                    <h3>About This Portal</h3>
                    <p>
                        This system is designed to manage sponsorship records, Child and Youth follow-up, reporting, and cluster-based coordination in one organized digital platform.
                    </p>

                    <ul class="hero-points">
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>Support Child and Youths sponsored by a church or a private individual</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>Strengthen record keeping and reporting for Kasulu & Kigoma Northern Clusters</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>Promote transformation through Christ-centered sponsorship ministry</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="mission" class="mission">
        <div class="container">
            <div class="section-grid">
                <div>
                    <span class="section-tag">Mission Statement</span>

                    <h2 class="section-title">
                        Local sponsorship is a ministry of church responsibility, faithful care, and transformation.
                    </h2>

                    <p class="section-text">
                        Local Sponsorship involves supporting Child and Youths from within their own context, where they are sponsored by churches, ministries, or individuals. The purpose is not only to provide support, but to walk with each Child and Youth through care, discipleship, and practical follow-up in Jesus' name.
                    </p>

                    <p class="section-text">
                        Through this ministry, we seek to ensure that every sponsored Child and Youth is known, followed up, encouraged, and supported through accurate records, strong coordination, and faithful service across Kasulu & Kigoma Northern Clusters.
                    </p>
                </div>

                <div class="highlight-card">
                    <p class="section-text">
                        The Local Sponsorship Portal exists to strengthen this ministry by making it easier to manage Child and Youth information, sponsorship progress, cluster records, and useful reports.
                    </p>

                    <p class="section-text">
                        It is a practical tool that supports the larger mission of helping Child and Youths experience dignity, hope, care, and transformation through the love of Christ and the commitment of local churches and sponsors.
                    </p>

                    <p class="section-text">
                        Our goal is clear: to support each Child and Youth in a way that strengthens care, accountability, and Christ-centered transformation.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <div class="features-header">
                <h2>What the portal helps you do</h2>
                <p>
                    Built for practical local sponsorship work, this system helps clusters, centers, and administrators manage records in a more organized, accountable, and effective way.
                </p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-users"></i></div>
                    <h3>Child and Youth Records</h3>
                    <p>
                        Store and manage Child and Youth details, sponsorship status, and cluster-linked records in one place.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Progress Tracking</h3>
                    <p>
                        Follow Child and Youth updates, sponsorship progress, and important record changes with better visibility.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-magnifying-glass"></i></div>
                    <h3>Search & Review</h3>
                    <p>
                        Quickly search Child and Youths and open detailed profiles for easier monitoring and review.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-file-lines"></i></div>
                    <h3>Reporting</h3>
                    <p>
                        Generate clean cluster-based and center-based reports to support planning, accountability, and decision-making.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div>
                    <h3>Local Sponsorship Portal</h3>
                    <p>
                        A digital platform for managing internal Child and Youth sponsorship, Child and Youth records, cluster activities, and reporting for churches, ministries, and local sponsors.
                    </p>
                </div>

                <div>
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        @if (Route::has('register'))
                            <li><a href="{{ route('register') }}">Register</a></li>
                        @endif
                        @if (Route::has('login'))
                            <li><a href="{{ route('login') }}">Login</a></li>
                        @endif
                    </ul>
                </div>

                <div>
                    <h4>Contact Info</h4>

                    <div class="contact-item">
                        <i class="fas fa-building"></i>
                        <span>Local Sponsorship Management System</span>
                    </div>

                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>info@localsponsorshipportal.org</span>
                    </div>

                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>
                            +255 623 722 507 / Emmanuel Russota
                            <br>
                            ERussota@tz.ci.org
                        </span>
                    </div>

                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Kasulu & Kigoma Northern Clusters, Tanzania</span>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <span><strong>Developed and maintained by Idriss ICT Services, Copyright &copy; 2026 Local Sponsorship Portal. All rights reserved.</strong></span>
                <a href="{{ url('/') }}"><strong>Privacy Statement</strong></a>
                <a href="{{ url('/') }}"><strong>Terms of Use</strong></a>
            </div>
        </div>
    </footer>
    <script>
        (() => {
            const logo = document.getElementById('welcome-rotating-logo');

            if (!logo) {
                return;
            }

            const logos = JSON.parse(logo.dataset.logos || '[]');

            if (logos.length < 2) {
                return;
            }

            let currentIndex = 0;

            setInterval(() => {
                currentIndex = (currentIndex + 1) % logos.length;
                logo.style.opacity = '0';

                setTimeout(() => {
                    logo.src = logos[currentIndex];
                    logo.style.opacity = '1';
                }, 250);
            }, 5000);
        })();
    </script>
</body>
</html>
