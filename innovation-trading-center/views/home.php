<style>
body, html {
    height: 100%;
    margin: 0;
    padding: 0;
}
.hero-bg {
    position: relative;
    min-height: 100vh;
    width: 100vw;
    background: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1600&q=80') center center/cover no-repeat;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    color: #fff;
    overflow: hidden;
    padding: 0;
    margin: 0;
}
.hero-bg::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(30, 30, 30, 0.55);
    z-index: 1;
}
.hero-glass {
    position: relative;
    z-index: 2;
    max-width: 800px;
    min-width: 340px;
    margin-left: 2vw;
    padding: 4rem 3rem 3rem 3rem;
    border-radius: 2.5rem;
    background: linear-gradient(120deg, rgba(255,255,255,0.18) 60%, rgba(0,123,255,0.10) 100%);
    box-shadow: 0 12px 48px 0 rgba(31, 38, 135, 0.22), 0 0 0 4px rgba(0,123,255,0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border: 2.5px solid rgba(255,255,255,0.22);
    animation: fadeInUp 1.2s cubic-bezier(.23,1.01,.32,1) both;
    overflow: hidden;
}
.hero-glass::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 2.5rem;
    pointer-events: none;
    background: linear-gradient(120deg, rgba(0,123,255,0.10) 0%, rgba(255,255,255,0.08) 100%);
    z-index: 0;
    animation: glassGlow 4s ease-in-out infinite alternate;
}
@keyframes glassGlow {
    from { box-shadow: 0 0 32px 0 rgba(0,123,255,0.10); }
    to { box-shadow: 0 0 64px 0 rgba(0,123,255,0.18); }
}
.hero-glass > * { position: relative; z-index: 1; }
.hero-glass h1 {
    font-size: 3.2rem;
    line-height: 1.1;
    font-weight: 800;
    letter-spacing: -1px;
}
.hero-glass p.lead {
    font-size: 1.35rem;
    margin-bottom: 2.2rem;
}
.hero-glass .d-flex.gap-4 > div .h4 {
    font-size: 2rem;
}
.hero-glass .d-flex.gap-4 > div .small {
    font-size: 1.1rem;
    opacity: 0.85;
}
@media (max-width: 900px) {
    .hero-3d { display: none; }
    .hero-glass { margin-left: 0; max-width: 98vw; padding: 2.2rem 1.2rem; }
    .hero-glass h1 { font-size: 2.1rem; }
}
.hero-btn {
    transition: all 0.25s cubic-bezier(.4,0,.2,1);
    box-shadow: 0 2px 12px 0 rgba(0,123,255,0.10);
    font-weight: 600;
    letter-spacing: 0.5px;
    border-radius: 2rem;
    padding-left: 2rem;
    padding-right: 2rem;
    font-size: 1.15rem;
}
.hero-btn-primary {
    background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
    color: #fff;
    border: none;
}
.hero-btn-primary:hover, .hero-btn-primary:focus {
    background: linear-gradient(90deg, #00c6ff 0%, #007bff 100%);
    color: #fff;
    transform: translateY(-2px) scale(1.04) rotate(-1deg);
    box-shadow: 0 4px 24px 0 rgba(0,123,255,0.18);
}
.hero-btn-outline {
    background: rgba(255,255,255,0.08);
    color: #fff;
    border: 2px solid #fff;
}
.hero-btn-outline:hover, .hero-btn-outline:focus {
    background: #fff;
    color: #007bff;
    border-color: #007bff;
    transform: translateY(-2px) scale(1.04) rotate(1deg);
    box-shadow: 0 4px 24px 0 rgba(0,123,255,0.10);
}
.features-section {
    background: linear-gradient(120deg, #f8f9fa 60%, #e3f0ff 100%);
    padding: 4rem 0 2.5rem 0;
}
.feature-card {
    border-radius: 1.5rem;
    box-shadow: 0 4px 24px 0 rgba(0,123,255,0.07);
    transition: transform 0.25s cubic-bezier(.4,0,.2,1), box-shadow 0.25s;
    background: #fff;
    border: none;
    position: relative;
    overflow: hidden;
}
.feature-card:hover {
    transform: translateY(-8px) scale(1.03);
    box-shadow: 0 8px 32px 0 rgba(0,123,255,0.13);
}
.feature-icon {
    font-size: 2.8rem;
    margin-bottom: 1rem;
    display: inline-block;
    background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.stats-section {
    background: #fff;
    padding: 3rem 0 2rem 0;
}
.stat-card {
    border-radius: 1.2rem;
    background: #f8faff;
    box-shadow: 0 2px 12px 0 rgba(0,123,255,0.06);
    padding: 2rem 1rem 1.5rem 1rem;
    margin: 0 0.5rem;
    min-width: 160px;
}
.stat-number {
    font-size: 2.8rem;
    font-weight: 800;
    margin-bottom: 0.3rem;
    letter-spacing: -1px;
    line-height: 1.1;
    display: block;
}
.stat-label {
    font-size: 1.1rem;
    color: #6c757d;
    font-weight: 500;
}
.cta-section {
    position: relative;
    background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
    color: #fff;
    padding: 4rem 0 3rem 0;
    overflow: hidden;
    text-align: center;
}
.cta-section h4 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
}
.cta-section .btn {
    font-size: 1.25rem;
    padding: 0.9rem 2.5rem;
    border-radius: 2rem;
    font-weight: 600;
    box-shadow: 0 2px 16px 0 rgba(0,0,0,0.10);
    transition: transform 0.2s, box-shadow 0.2s;
}
.cta-section .btn:hover {
    transform: scale(1.06) translateY(-2px);
    box-shadow: 0 8px 32px 0 rgba(0,123,255,0.18);
}
.cta-wave {
    position: absolute;
    left: 0; right: 0; bottom: 0;
    width: 100%;
    height: 60px;
    z-index: 1;
}
@media (max-width: 900px) {
    .features-section { padding: 2.2rem 0 1.2rem 0; }
    .stats-section { padding: 1.5rem 0 1rem 0; }
    .cta-section { padding: 2.2rem 0 1.5rem 0; }
    .stat-card { min-width: 120px; padding: 1.2rem 0.5rem 1rem 0.5rem; }
}
</style>

<div class="hero-bg">
    <div class="hero-glass">
        <div class="mb-2 text-uppercase small fw-bold" style="letter-spacing:2px;opacity:0.8;">01 / Welcome</div>
        <h1 class="display-3 fw-bold mb-3">Back to Innovation</h1>
        <p class="lead mb-4">Change the way you experience innovation in Ethiopia. Discover, connect, and grow with the Innovation Trading Center Platform.</p>
        <div class="d-flex flex-wrap gap-3 mb-4">
            <a href="/register" class="btn hero-btn hero-btn-primary btn-lg shadow">Get Started</a>
            <a href="/innovations" class="btn hero-btn hero-btn-outline btn-lg">Browse Innovations</a>
        </div>
        <div class="d-flex gap-4">
            <div>
                <div class="fw-bold h4 mb-0">120+</div>
                <div class="small text-light">Innovations</div>
            </div>
            <div>
                <div class="fw-bold h4 mb-0">80+</div>
                <div class="small text-light">Sponsors</div>
            </div>
            <div>
                <div class="fw-bold h4 mb-0">300+</div>
                <div class="small text-light">Connections</div>
            </div>
        </div>
    </div>
    <!-- 3D Placeholder (SVG) -->
    <div class="hero-3d">
        <svg viewBox="0 0 200 200" width="100%" height="100%">
            <defs>
                <radialGradient id="grad" cx="50%" cy="50%" r="50%">
                    <stop offset="0%" stop-color="#fff" stop-opacity="0.8"/>
                    <stop offset="100%" stop-color="#007bff" stop-opacity="0.7"/>
                </radialGradient>
            </defs>
            <ellipse cx="100" cy="120" rx="80" ry="40" fill="url(#grad)"/>
            <circle cx="100" cy="80" r="60" fill="#fff" fill-opacity="0.15"/>
            <circle cx="100" cy="80" r="50" fill="url(#grad)"/>
            <ellipse cx="100" cy="60" rx="30" ry="12" fill="#fff" fill-opacity="0.3"/>
        </svg>
    </div>
</div>

<!-- Features Section -->
<div class="features-section">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <span class="feature-icon"><i class="bi bi-lightbulb"></i></span>
                        <h5 class="card-title fw-bold">For Innovators</h5>
                        <p class="card-text">Showcase your ideas, connect with sponsors, and access resources to bring your innovations to life.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <span class="feature-icon"><i class="bi bi-people"></i></span>
                        <h5 class="card-title fw-bold">For Sponsors</h5>
                        <p class="card-text">Discover promising projects, support Ethiopian talent, and invest in the future of innovation.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <span class="feature-icon"><i class="bi bi-chat-dots"></i></span>
                        <h5 class="card-title fw-bold">Connect & Collaborate</h5>
                        <p class="card-text">Message, network, and collaborate with innovators, sponsors, and the admin team in one place.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="stats-section">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-6 col-md-3 mb-3">
                <div class="stat-card">
                    <span class="stat-number text-primary" id="stat-innovations">120+</span>
                    <div class="stat-label">Innovations</div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stat-card">
                    <span class="stat-number text-success" id="stat-sponsors">80+</span>
                    <div class="stat-label">Sponsors</div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stat-card">
                    <span class="stat-number text-info" id="stat-connections">300+</span>
                    <div class="stat-label">Connections</div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stat-card">
                    <span class="stat-number text-warning" id="stat-support">24/7</span>
                    <div class="stat-label">Support</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action Banner -->
<div class="cta-section">
    <div class="container position-relative" style="z-index:2;">
        <h4 class="mb-3">Ready to join Ethiopia's innovation movement?</h4>
        <a href="/register" class="btn btn-light btn-lg">Create Your Free Account</a>
    </div>
    <svg class="cta-wave" viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#fff" fill-opacity="1" d="M0,32L48,37.3C96,43,192,53,288,58.7C384,64,480,64,576,53.3C672,43,768,21,864,16C960,11,1056,21,1152,32C1248,43,1344,53,1392,58.7L1440,64L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>
</div>

<!-- Footer -->
<footer class="container py-4 text-center text-muted small">
    &copy; <?= date('Y') ?> Innovation Trading Center Platform. All rights reserved.
</footer>

<script>
// Animate headline and subheadline
window.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.hero-glass h1').style.opacity = 0;
    document.querySelector('.hero-glass p').style.opacity = 0;
    setTimeout(function() {
        document.querySelector('.hero-glass h1').style.transition = 'opacity 1s';
        document.querySelector('.hero-glass h1').style.opacity = 1;
    }, 300);
    setTimeout(function() {
        document.querySelector('.hero-glass p').style.transition = 'opacity 1s';
        document.querySelector('.hero-glass p').style.opacity = 1;
    }, 800);

    // Animated count-up for stats
    function animateCount(id, target, duration) {
        var el = document.getElementById(id);
        if (!el) return;
        var start = 0;
        var startTime = null;
        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var value = Math.floor(progress * target);
            el.textContent = value + (id === 'stat-support' ? '/7' : '+');
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = target + (id === 'stat-support' ? '/7' : '+');
            }
        }
        requestAnimationFrame(step);
    }
    animateCount('stat-innovations', 120, 1200);
    animateCount('stat-sponsors', 80, 1200);
    animateCount('stat-connections', 300, 1200);
    // Support is static
});
</script> 