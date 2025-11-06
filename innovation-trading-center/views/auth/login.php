<style>
body, html {
    height: 100%;
    margin: 0;
    padding: 0;
}
.login-bg {
    min-height: 100vh;
    width: 100vw;
    background: url('https://images.unsplash.com/photo-1464983953574-0892a716854b?auto=format&fit=crop&w=1200&q=80') center center/cover no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}
.login-bg::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(30, 30, 30, 0.55);
    z-index: 1;
}
.login-container {
    position: relative;
    z-index: 2;
    display: flex;
    width: 100vw;
    max-width: 1100px;
    min-height: 600px;
    background: none;
    box-shadow: none;
}
.login-left {
    flex: 1 1 0;
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 3rem 2.5rem 3rem 3.5rem;
}
.login-left h1 {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 1.2rem;
    letter-spacing: -1px;
}
.login-left p {
    font-size: 1.15rem;
    opacity: 0.92;
    margin-bottom: 2.2rem;
}
.login-social {
    margin-top: 1.5rem;
}
.login-social a {
    color: #fff;
    font-size: 1.5rem;
    margin-right: 1.2rem;
    opacity: 0.8;
    transition: color 0.2s, opacity 0.2s;
}
.login-social a:hover { color: #ff9800; opacity: 1; }
.login-right {
    flex: 1 1 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 3.5rem 3rem 2.5rem;
}
.login-card {
    background: rgba(255,255,255,0.13);
    border-radius: 1.5rem;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1.5px solid rgba(255,255,255,0.18);
    padding: 2.5rem 2rem 2rem 2rem;
    width: 100%;
    max-width: 370px;
    color: #fff;
    position: relative;
}
.login-card label { color: #fff; font-weight: 500; }
.login-card .form-control { border-radius: 0.7rem; background: rgba(255,255,255,0.85); color: #222; }
.login-card .form-control:focus { box-shadow: 0 0 0 2px #007bff33; }
.login-card .form-check-label { color: #fff; }
.login-card .btn-primary {
    background: linear-gradient(90deg, #ff9800 0%, #ff5722 100%);
    border: none;
    border-radius: 2rem;
    font-weight: 700;
    font-size: 1.15rem;
    padding: 0.7rem 0;
    margin-top: 0.7rem;
    box-shadow: 0 2px 12px 0 rgba(255,152,0,0.10);
    transition: background 0.2s, transform 0.2s;
}
.login-card .btn-primary:hover {
    background: linear-gradient(90deg, #ff5722 0%, #ff9800 100%);
    transform: translateY(-2px) scale(1.03);
}
.login-card .form-text, .login-card .form-link {
    color: #fff;
    opacity: 0.85;
    font-size: 0.97rem;
}
.login-card .form-link:hover { color: #ff9800; text-decoration: underline; }
@media (max-width: 900px) {
    .login-container { flex-direction: column; min-height: 0; max-width: 98vw; }
    .login-left, .login-right { padding: 2rem 1.2rem; }
    .login-left h1 { font-size: 2.1rem; }
}
</style>

<div class="login-bg">
    <div class="login-container">
        <div class="login-left">
            <h1>Welcome<br>Back</h1>
            <p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum...</p>
            <div class="login-social">
                <a href="#"><i class="bi bi-facebook"></i></a>
                <a href="#"><i class="bi bi-twitter"></i></a>
                <a href="#"><i class="bi bi-google"></i></a>
                <a href="#"><i class="bi bi-github"></i></a>
            </div>
        </div>
        <div class="login-right">
            <form class="login-card" method="post" action="/login">
                <h3 class="mb-4 fw-bold">Sign in</h3>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="/password-reset" class="form-link">Password reset</a>
                </div>
                <button type="submit" class="btn btn-primary w-100">Sign in now</button>
                <div class="mt-3 text-center">
                    <a href="/register" class="form-link">Don't have an account? Sign up here</a>
                </div>
                <div class="mt-3 text-center">
                    <small class="form-text">By click on 'Sign in now' you agree to <a href="#" class="form-link">Terms of Service</a> | <a href="#" class="form-link">Privacy Policy</a></small>
                </div>
            </form>
        </div>
    </div>
</div> 