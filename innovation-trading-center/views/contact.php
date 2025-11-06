<style>
body.contact-page {
    background: #f8f9fa;
}
.contact-hero {
    min-height: 38vh;
    width: 100vw;
    background: linear-gradient(120deg, #00c6ff 0%, #007bff 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    position: relative;
    overflow: hidden;
    padding: 5rem 1rem 2.5rem 1rem;
}
.contact-hero h1 {
    font-size: 2.7rem;
    font-weight: 900;
    margin-bottom: 1.1rem;
    letter-spacing: -1px;
    animation: fadeInDown 1.1s cubic-bezier(.23,1.01,.32,1) both;
}
.contact-hero p {
    font-size: 1.18rem;
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
.contact-hero-wave {
    position: absolute;
    left: 0; right: 0; bottom: 0;
    width: 100%;
    height: 80px;
    z-index: 1;
}
.contact-section {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 2.5rem;
    margin-top: 2.5rem;
    margin-bottom: 2rem;
}
.contact-form-card {
    background: rgba(255,255,255,0.18);
    border-radius: 2rem;
    box-shadow: 0 12px 48px 0 rgba(31, 38, 135, 0.13), 0 0 0 4px rgba(0,123,255,0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border: 2.5px solid rgba(255,255,255,0.22);
    padding: 2.5rem 2rem 2rem 2rem;
    width: 100%;
    max-width: 420px;
    color: #222;
    position: relative;
    z-index: 2;
    animation: floatCard 1.2s cubic-bezier(.23,1.01,.32,1) both;
}
@keyframes floatCard {
    from { opacity: 0; transform: translateY(60px) scale(0.98); }
    to { opacity: 1; transform: none; }
}
.contact-form-card label { color: #222; font-weight: 500; }
.contact-form-card .form-control { border-radius: 0.7rem; background: rgba(255,255,255,0.85); color: #222; }
.contact-form-card .form-control:focus { box-shadow: 0 0 0 2px #007bff33; }
.contact-form-card .btn-primary {
    background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
    border: none;
    border-radius: 2rem;
    font-weight: 700;
    font-size: 1.1rem;
    padding: 0.7rem 0;
    margin-top: 0.7rem;
    box-shadow: 0 2px 12px 0 rgba(0,123,255,0.10);
    transition: background 0.2s, transform 0.2s;
}
.contact-form-card .btn-primary:hover {
    background: linear-gradient(90deg, #00c6ff 0%, #007bff 100%);
    transform: translateY(-2px) scale(1.03);
}
.contact-info {
    background: #fff;
    border-radius: 1.5rem;
    box-shadow: 0 4px 24px 0 rgba(0,123,255,0.07);
    padding: 2.5rem 2rem 2rem 2rem;
    max-width: 350px;
    color: #222;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
    gap: 1.2rem;
    z-index: 2;
}
.contact-info h5 { font-weight: 700; margin-bottom: 0.5rem; }
.contact-info a { color: #007bff; text-decoration: none; }
.contact-info a:hover { text-decoration: underline; }
.contact-social {
    margin-top: 1.2rem;
}
.contact-social a {
    color: #007bff;
    font-size: 1.5rem;
    margin-right: 1.2rem;
    opacity: 0.8;
    transition: color 0.2s, opacity 0.2s;
}
.contact-social a:hover { color: #00c6ff; opacity: 1; }
@media (max-width: 900px) {
    .contact-section { flex-direction: column; gap: 1.2rem; }
    .contact-form-card, .contact-info { max-width: 98vw; }
}
</style>
<div class="contact-hero">
    <div style="position:relative;z-index:2;">
        <h1>Contact Us</h1>
        <p>Have a question, suggestion, or want to partner with us? Reach out and our team will get back to you soon!</p>
    </div>
    <svg class="contact-hero-wave" viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#f8f9fa" fill-opacity="1" d="M0,32L48,37.3C96,43,192,53,288,58.7C384,64,480,64,576,53.3C672,43,768,21,864,16C960,11,1056,21,1152,32C1248,43,1344,53,1392,58.7L1440,64L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>
</div>
<div class="contact-section">
    <form class="contact-form-card" method="post" action="/contact">
        <h4 class="mb-4 fw-bold">Send a Message</h4>
        <div class="mb-3">
            <label for="contact_name" class="form-label">Your Name</label>
            <input type="text" class="form-control" id="contact_name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="contact_email" class="form-label">Your Email</label>
            <input type="email" class="form-control" id="contact_email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="contact_message" class="form-label">Message</label>
            <textarea class="form-control" id="contact_message" name="message" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-100">Send Message</button>
    </form>
    <div class="contact-info">
        <h5>Contact Information</h5>
        <div><strong>Email:</strong> <a href="mailto:info@inotrade.com">info@inotrade.com</a></div>
        <div><strong>Phone:</strong> <a href="tel:+251900000000">+251 900 000 000</a></div>
        <div><strong>Address:</strong> Addis Ababa, Ethiopia</div>
        <div class="contact-social">
            <a href="#"><i class="bi bi-facebook"></i></a>
            <a href="#"><i class="bi bi-twitter"></i></a>
            <a href="#"><i class="bi bi-linkedin"></i></a>
        </div>
    </div>
</div> 