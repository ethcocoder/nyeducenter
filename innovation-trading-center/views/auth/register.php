<style>
body, html {
    height: 100%;
    margin: 0;
    padding: 0;
}
.register-bg {
    min-height: 100vh;
    width: 100vw;
    background: url('https://images.unsplash.com/photo-1464983953574-0892a716854b?auto=format&fit=crop&w=1200&q=80') center center/cover no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}
.register-bg::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(30, 30, 30, 0.55);
    z-index: 1;
}
.register-container {
    position: relative;
    z-index: 2;
    display: flex;
    width: 100vw;
    max-width: 1200px;
    min-height: 700px;
    background: none;
    box-shadow: none;
}
.register-left {
    flex: 1 1 0;
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 3rem 2.5rem 3rem 3.5rem;
}
.register-left h1 {
    font-size: 2.7rem;
    font-weight: 800;
    margin-bottom: 1.2rem;
    letter-spacing: -1px;
}
.register-left p {
    font-size: 1.15rem;
    opacity: 0.92;
    margin-bottom: 2.2rem;
}
.register-right {
    flex: 1 1 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 3.5rem 3rem 2.5rem;
}
.register-card {
    background: rgba(255,255,255,0.13);
    border-radius: 1.5rem;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1.5px solid rgba(255,255,255,0.18);
    padding: 2.5rem 2rem 2rem 2rem;
    width: 100%;
    max-width: 470px;
    color: #fff;
    position: relative;
}
.register-card label { color: #fff; font-weight: 500; }
.register-card .form-control, .register-card .form-select { border-radius: 0.7rem; background: rgba(255,255,255,0.85); color: #222; }
.register-card .form-control:focus, .register-card .form-select:focus { box-shadow: 0 0 0 2px #007bff33; }
.register-card .form-check-label { color: #fff; }
.register-card .btn-primary {
    background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
    border: none;
    border-radius: 2rem;
    font-weight: 700;
    font-size: 1.15rem;
    padding: 0.7rem 0;
    margin-top: 0.7rem;
    box-shadow: 0 2px 12px 0 rgba(0,123,255,0.10);
    transition: background 0.2s, transform 0.2s;
}
.register-card .btn-primary:hover {
    background: linear-gradient(90deg, #00c6ff 0%, #007bff 100%);
    transform: translateY(-2px) scale(1.03);
}
.register-card .form-text, .register-card .form-link {
    color: #fff;
    opacity: 0.85;
    font-size: 0.97rem;
}
.register-card .form-link:hover { color: #007bff; text-decoration: underline; }
@media (max-width: 900px) {
    .register-container { flex-direction: column; min-height: 0; max-width: 98vw; }
    .register-left, .register-right { padding: 2rem 1.2rem; }
    .register-left h1 { font-size: 2.1rem; }
}
</style>

<div class="register-bg">
    <div class="register-container">
        <div class="register-left">
            <h1>Join the Innovation<br>Trading Center</h1>
            <p>Create your free account to showcase your innovations, connect with sponsors, and be part of Ethiopia's growing innovation ecosystem. Registration is quick and easy!</p>
        </div>
        <div class="register-right">
            <form class="register-card" method="post" action="/register">
                <h3 class="mb-4 fw-bold">Create Your Account</h3>
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Selamawit Bekele" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="e.g. selamawit@email.com" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Choose a strong password" required>
                </div>
                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Re-enter your password" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Register as</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="innovator">Innovator</option>
                        <option value="sponsor">Sponsor/Investor</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="organization" class="form-label">Organization <span class="text-muted">(optional)</span></label>
                    <input type="text" class="form-control" id="organization" name="organization" placeholder="e.g. Addis Tech Hub">
                </div>
                <div class="mb-3">
                    <label for="bio" class="form-label">Short Bio <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" id="bio" name="bio" rows="2" placeholder="Tell us a bit about yourself and your interests"></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
                <div class="mt-3 text-center">
                    <a href="/login" class="form-link">Already have an account? Login here</a>
                </div>
            </form>
        </div>
    </div>
</div> 