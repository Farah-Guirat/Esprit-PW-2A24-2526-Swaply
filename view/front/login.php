<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Swaply - Sign In</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f7f8fa;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* NAVBAR */
    .navbar {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      padding: 14px 32px;
      border-bottom: 1px solid #e8e8e8;
      background: #fff;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 24px;
    }

    .nav-link {
      font-size: 12px;
      color: #888;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 5px;
      text-decoration: none;
      letter-spacing: 0.3px;
    }

    .nav-link.active {
      color: #222;
      font-weight: 600;
    }

    .nav-link svg {
      width: 12px;
      height: 12px;
    }

    /* MAIN LAYOUT */
    .main {
      display: flex;
      flex: 1;
      min-height: calc(100vh - 53px);
    }

    /* LEFT - FORM */
    .left {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 60px 40px;
      background: #fff;
    }

    .form-box {
      width: 100%;
      max-width: 340px;
    }

    .form-title {
      font-size: 28px;
      font-weight: 700;
      color: #4FD1C5;
      margin-bottom: 8px;
    }

    .form-sub {
      font-size: 13px;
      color: #aaa;
      margin-bottom: 32px;
    }

    .form-label {
      font-size: 13px;
      font-weight: 600;
      color: #333;
      margin-bottom: 7px;
      display: block;
    }

    .form-input {
      width: 100%;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 11px 15px;
      font-size: 13px;
      color: #333;
      background: #fff;
      margin-bottom: 20px;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-input::placeholder {
      color: #bbb;
    }

    .form-input:focus {
      border-color: #4FD1C5;
      box-shadow: 0 0 0 3px rgba(79, 209, 197, 0.15);
    }

    .btn-signin {
      width: 100%;
      background: #4FD1C5;
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 13px;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 1px;
      cursor: pointer;
      margin-bottom: 20px;
      transition: background 0.2s, transform 0.1s;
    }

    .btn-signin:hover {
      background: #38B2AC;
    }

    .btn-signin:active {
      transform: scale(0.98);
    }

    .signup-row {
      text-align: center;
      font-size: 13px;
      color: #aaa;
    }

    .signup-link {
      color: #4FD1C5;
      cursor: pointer;
      font-weight: 600;
      text-decoration: none;
    }

    .signup-link:hover {
      text-decoration: underline;
    }

    /* RIGHT - LOGO PANEL */
    .right {
      width: 45%;
      background: #4FD1C5;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    .right svg.waves {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      opacity: 0.25;
    }

    .logo-img {
      width: 200px;
      height: 200px;
      object-fit: contain;
      z-index: 2;
      position: relative;
      border-radius: 50%;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
      .right { display: none; }
      .left { padding: 40px 24px; }
    }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar">
    <div class="nav-links">
      <a class="nav-link" href="#">
        <svg viewBox="0 0 12 12" fill="currentColor"><circle cx="6" cy="4" r="2.5"/><path d="M1 10c0-2.5 2.2-4.5 5-4.5s5 2 5 4.5"/></svg>
        PROFILE
      </a>
      <a class="nav-link" href="register.php">SIGN UP</a>
      <a class="nav-link active" href="#">
        <svg viewBox="0 0 12 12" fill="currentColor"><path d="M2 6h8M6 2l4 4-4 4" stroke="currentColor" stroke-width="1.2" fill="none"/></svg>
        SIGN IN
      </a>
    </div>
  </nav>

  <!-- MAIN -->
  <div class="main">




    <!-- FORM -->
    <div class="left">
      <div class="form-box">
        <h2 class="form-title">Welcome Back</h2>



        <p class="form-sub">Enter your email and password to sign in</p>

    <form method="POST" action="../../controller/UserC.php" onsubmit="return validateLogin()">

    <label class="form-label">Email</label>
    <input class="form-input" type="text" name="email" id="email" placeholder="Your email address" />

    <label class="form-label">Password</label>
    <input class="form-input" type="password" name="password" id="password" placeholder="Your password" />

    <!-- Forgot Password -->
    <div style="text-align: right; margin-top: -12px; margin-bottom: 20px;">
        <a href="forgot_password.php"
           style="font-size: 12px; color: #4FD1C5; text-decoration: underline; font-weight: 600;">
            Forgot password?
        </a>
    </div>

    <p id="error-msg" style="color:red; margin-bottom:10px;"></p>

    <button type="submit" name="login" class="btn-signin">SIGN IN</button>

    </form>



        

        <p class="signup-row">
          Don't have an account? <a class="signup-link" href="register.php">Sign up</a>
        </p>
      </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right">
      <svg class="waves" viewBox="0 0 400 500" fill="none" xmlns="http://www.w3.org/2000/svg">
        <ellipse cx="300" cy="100" rx="200" ry="300" stroke="white" stroke-width="1.5" fill="none"/>
        <ellipse cx="100" cy="400" rx="250" ry="200" stroke="white" stroke-width="1" fill="none"/>
        <ellipse cx="350" cy="350" rx="150" ry="220" stroke="white" stroke-width="1" fill="none"/>
      </svg>
      <!-- Remplacez le src ci-dessous par le chemin de votre logo -->
      <img class="logo-img" src="logo.png" alt="Swaply Logo" />
    </div>

  </div>

<script>
function validateLogin() {
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();
    let errorMsg = document.getElementById("error-msg");

    errorMsg.innerHTML = "";

    if (email === "" || password === "") {
        errorMsg.innerHTML = "Veuillez remplir tous les champs.";
        return false;
    }

    let emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
    if (!email.match(emailPattern)) {
        errorMsg.innerHTML = "Format email invalide.";
        return false;
    }

    return true;
}


window.onload = function() {
    let params = new URLSearchParams(window.location.search);
    let errorMsg = document.getElementById("error-msg");

    if (params.get("error") == "1") {
        errorMsg.innerHTML = "Veuillez vérifier les informations de connexion.";

        // نحيو error من URL (باش ما يعاودش يطلع)
        window.history.replaceState({}, document.title, window.location.pathname);
    }
};
</script>

</body>
</html>