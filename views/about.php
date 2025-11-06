<style>
body.about-page {
    background: #f8f9fa;
}
.about-hero {
    min-height: 48vh;
    width: 100vw;
    background: linear-gradient(120deg, #007bff 0%, #00c6ff 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    position: relative;
    overflow: hidden;
    padding: 5.5rem 1rem 2.5rem 1rem;
}
.about-hero h1 {
    font-size: 3.2rem;
    font-weight: 900;
    margin-bottom: 1.1rem;
    letter-spacing: -1px;
    animation: fadeInDown 1.1s cubic-bezier(.23,1.01,.32,1) both;
}
.about-hero p {
    font-size: 1.35rem;
    opacity: 0.97;
    animation: fadeInUp 1.2s cubic-bezier(.23,1.01,.32,1) both;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-40px); }
    to { opacity: 1; transform: none; }
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: none; }
}
.about-hero-wave {
    position: absolute;
    left: 0; right: 0; bottom: 0;
    width: 100%;
    height: 80px;
    z-index: 1;
}
.about-story-card {
    background: rgba(255,255,255,0.18);
    border-radius: 2rem;
    box-shadow: 0 12px 48px 0 rgba(31, 38, 135, 0.13), 0 0 0 4px rgba(0,123,255,0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border: 2.5px solid rgba(255,255,255,0.22);
    margin-top: 2.5rem;
    margin-bottom: 2.5rem;
    padding: 3rem 2.5rem 2.5rem 2.5rem;
    max-width: 950px;
    margin-left: auto;
    margin-right: auto;
    position: relative;
    z-index: 2;
    animation: floatCard 1.2s cubic-bezier(.23,1.01,.32,1) both;
}
@keyframes floatCard {
    from { opacity: 0; transform: translateY(60px) scale(0.98); }
    to { opacity: 1; transform: none; }
}
.about-story-card h2 {
    font-size: 2.1rem;
    font-weight: 800;
    margin-bottom: 1.2rem;
    color: #222;
}
.about-story-card p {
    color: #222;
    font-size: 1.13rem;
    margin-bottom: 2.2rem;
}
.about-values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 2.2rem;
    margin-top: 1.5rem;
}
.about-value {
    text-align: center;
    padding: 1.2rem 0.5rem 1.2rem 0.5rem;
    border-radius: 1.2rem;
    background: rgba(255,255,255,0.7);
    box-shadow: 0 2px 12px 0 rgba(0,123,255,0.06);
    transition: transform 0.18s, box-shadow 0.18s;
    cursor: pointer;
}
.about-value:hover {
    transform: translateY(-6px) scale(1.04);
    box-shadow: 0 8px 32px 0 rgba(0,123,255,0.13);
}
.about-value i {
    font-size: 2.7rem;
    margin-bottom: 0.7rem;
    display: block;
    background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.about-value h5 {
    font-weight: 800;
    margin-bottom: 0.5rem;
    color: #007bff;
    font-size: 1.2rem;
}
.about-value p {
    color: #222;
    font-size: 1.05rem;
    opacity: 0.92;
}
@media (max-width: 900px) {
    .about-hero h1 { font-size: 2rem; }
    .about-story-card { padding: 1.2rem 0.7rem; }
    .about-values-grid { gap: 1.1rem; }
}
</style>
<div class="about-hero">
    <div style="position:relative;z-index:2;">
        <h1>About Innovation Trading Center</h1>
        <p>Empowering Ethiopian innovators and sponsors to connect, collaborate, and create a brighter future together.</p>
    </div>
    <svg class="about-hero-wave" viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#f8f9fa" fill-opacity="1" d="M0,32L48,37.3C96,43,192,53,288,58.7C384,64,480,64,576,53.3C672,43,768,21,864,16C960,11,1056,21,1152,32C1248,43,1344,53,1392,58.7L1440,64L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>
</div>
<div class="about-story-card">
    <h2>Our Story</h2>
    <p>Innovation Trading Center was founded to bridge the gap between brilliant Ethiopian innovators and the sponsors/investors who can help bring their ideas to life. Our mission is to create a thriving ecosystem where ideas grow, partnerships form, and innovation flourishes for the benefit of all.</p>
    <div class="about-values-grid">
        <div class="about-value">
            <i class="bi bi-lightbulb"></i>
            <h5>Inspire</h5>
            <p>We inspire creativity and support bold new ideas from all corners of Ethiopia.</p>
        </div>
        <div class="about-value">
            <i class="bi bi-people"></i>
            <h5>Connect</h5>
            <p>We connect innovators, sponsors, and the community to foster collaboration and growth.</p>
        </div>
        <div class="about-value">
            <i class="bi bi-graph-up"></i>
            <h5>Empower</h5>
            <p>We empower our users with resources, mentorship, and opportunities to succeed.</p>
        </div>
    </div>
</div> 