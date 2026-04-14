<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Swaply - Sign Up</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f7f8fa;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .navbar {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      padding: 12px 28px;
      background: #4FD1C5;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .nav-link {
      font-size: 11px;
      color: rgba(255,255,255,0.85);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 4px;
      text-decoration: none;
    }

    .nav-link.active {
      color: #fff;
      font-weight: 700;
    }

    .hero {
      background: #4FD1C5;
      position: relative;
      overflow: hidden;
      padding: 36px 20px 80px;
      text-align: center;
    }

    .hero svg.waves {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      opacity: 0.2;
      pointer-events: none;
    }

    .hero-title {
      font-size: 26px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 10px;
      position: relative;
      z-index: 2;
    }

    .hero-sub {
      font-size: 13px;
      color: rgba(255,255,255,0.85);
      max-width: 280px;
      margin: 0 auto;
      line-height: 1.6;
      position: relative;
      z-index: 2;
    }

    .card-wrap {
      display: flex;
      justify-content: center;
      margin-top: -50px;
      padding: 0 20px 40px;
      background: #f7f8fa;
    }

    .card {
      background: #fff;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      padding: 28px 32px;
      width: 100%;
      max-width: 420px;
    }

    .register-title {
      font-size: 15px;
      font-weight: 700;
      color: #222;
      text-align: center;
      margin-bottom: 22px;
    }

    .row2 {
      display: flex;
      gap: 12px;
    }

    .row2 > div {
      flex: 1;
    }

    .form-label {
      font-size: 12px;
      font-weight: 600;
      color: #555;
      margin-bottom: 5px;
      display: block;
    }

    .form-input {
      width: 100%;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 10px 13px;
      font-size: 13px;
      color: #333;
      background: #fff;
      margin-bottom: 16px;
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

    select.form-input {
      color: #bbb;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23bbb' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      cursor: pointer;
    }

    select.form-input.selected {
      color: #333;
    }

    .btn-signup {
      width: 100%;
      background: #4FD1C5;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 13px;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.5px;
      cursor: pointer;
      margin-bottom: 16px;
      transition: background 0.2s, transform 0.1s;
    }

    .btn-signup:hover {
      background: #38B2AC;
    }

    .btn-signup:active {
      transform: scale(0.98);
    }

    .signin-row {
      text-align: center;
      font-size: 13px;
      color: #aaa;
    }

    .signin-link {
      color: #4FD1C5;
      cursor: pointer;
      font-weight: 600;
      text-decoration: none;
    }

    .signin-link:hover {
      text-decoration: underline;
    }

    @media (max-width: 480px) {
      .row2 { flex-direction: column; gap: 0; }
      .card { padding: 24px 18px; }
    }
  </style>
</head>
<body>

  <nav class="navbar">
    <div class="nav-links">
      <a class="nav-link" href="#">
        <svg width="11" height="11" viewBox="0 0 12 12" fill="currentColor">
          <circle cx="6" cy="4" r="2.5"/>
          <path d="M1 10c0-2.5 2.2-4.5 5-4.5s5 2 5 4.5"/>
        </svg>
        PROFILE
      </a>
      <a class="nav-link active" href="#">SIGN UP</a>
      <a class="nav-link" href="login.php">
        <svg width="11" height="11" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.2">
          <path d="M2 6h8M6 2l4 4-4 4"/>
        </svg>
        SIGN IN
      </a>
    </div>
  </nav>

  <div class="hero">
    <svg class="waves" viewBox="0 0 800 300" fill="none" xmlns="http://www.w3.org/2000/svg">
      <ellipse cx="600" cy="50" rx="350" ry="200" stroke="white" stroke-width="1.5" fill="none"/>
      <ellipse cx="200" cy="250" rx="400" ry="180" stroke="white" stroke-width="1" fill="none"/>
      <ellipse cx="700" cy="200" rx="250" ry="150" stroke="white" stroke-width="1" fill="none"/>
      <ellipse cx="100" cy="100" rx="200" ry="120" stroke="white" stroke-width="1" fill="none"/>
    </svg>
    <h1 class="hero-title">Welcome!</h1>
    <p class="hero-sub">Use these awesome forms to login or create new account in Swaply for free.</p>
  </div>

  <div class="card-wrap">
    <div class="card">

      <?php if (isset($_GET['error'])): ?>
        <p id="server-error-msg" style="color:red; margin-bottom:16px; font-weight:700;">
          <?php if ($_GET['error'] === 'empty'): ?>
            Veuillez remplir tous les champs.
          <?php elseif ($_GET['error'] === 'email'): ?>
            Format email invalide.
          <?php elseif ($_GET['error'] === 'duplicate'): ?>
            Cette adresse e-mail existe déjà.
          <?php else: ?>
            Une erreur s'est produite. Réessayez.
          <?php endif; ?>
        </p>
      <?php endif; ?>

      <form method="POST" action="/swaply/controller/UserC.php" onsubmit="return validateSignup()" novalidate>

      <p class="register-title">Register with</p>

      

      <div class="row2">
        <div>
          <label class="form-label">First Name</label>
          <input class="form-input" type="text" name="firstname" id="firstname" placeholder="First name" />
        </div>
        <div>
          <label class="form-label">Last Name</label>
          <input class="form-input" type="text" name="lastname" id="lastname" placeholder="Last name" />
        </div>
      </div>

      <label class="form-label">Email</label>
      <input class="form-input" type="text" name="email" id="email" placeholder="Your email address" />

      <label class="form-label">Phone Number</label>
      <input class="form-input" type="text" name="phone" id="phone" placeholder="Your phone number" />

      <div class="row2">
        <div>
          <label class="form-label">Date of Birth</label>
          <input class="form-input" type="text" name="date_naissance" id="date_naissance" placeholder="YYYY-MM-DD" />
        </div>
        <div>
          <label class="form-label">Gender</label>
          <select class="form-input" name="gender" id="gender">
            <option value="" disabled selected>Select</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
        </div>
      </div>

      <label class="form-label">Password</label>
      <input class="form-input" type="password" name="password" id="password" placeholder="Your password" />

      <p id="error-msg" style="color:red; margin-bottom:10px;"></p>

      <button type="submit" name="signup" class="btn-signup">SIGN UP</button>


      </form>

      <p class="signin-row">
        Already have an account? <a class="signin-link" href="login.php">Sign in</a>
      </p>
    </div>
  </div>


  <script>
function validateSignup() {

    let firstname = document.getElementById("firstname").value.trim();
    let lastname = document.getElementById("lastname").value.trim();
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();
    let phone = document.getElementById("phone").value.trim();
    let dateNaissance = document.getElementById("date_naissance").value.trim();
    let gender = document.getElementById("gender").value;

    let errorMsg = document.getElementById("error-msg");

    errorMsg.innerHTML = "";

    if (firstname === "" || lastname === "" || email === "" || password === "" || phone === "" || dateNaissance === "" || gender === "") {
        errorMsg.innerHTML = "Veuillez remplir tous les champs.";
        return false;
    }

    let emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
    if (!email.match(emailPattern)) {
        errorMsg.innerHTML = "Format email invalide.";
        return false;
    }

    if (phone.length > 12 || !/^[0-9]+$/.test(phone)) {
        errorMsg.innerHTML = "Numéro de téléphone invalide.";
        return false;
    }

    let datePattern = /^\d{4}-\d{2}-\d{2}$/;
    if (!datePattern.test(dateNaissance)) {
        errorMsg.innerHTML = "Le format de la date doit être YYYY-MM-DD.";
        return false;
    }

    let dateParts = dateNaissance.split("-");
    let year = parseInt(dateParts[0], 10);
    let month = parseInt(dateParts[1], 10);
    let day = parseInt(dateParts[2], 10);
    let testDate = new Date(dateNaissance);

    if (testDate.getFullYear() !== year || testDate.getMonth() + 1 !== month || testDate.getDate() !== day) {
        errorMsg.innerHTML = "Date de naissance invalide.";
        return false;
    }

    if (testDate > new Date()) {
        errorMsg.innerHTML = "La date de naissance ne peut pas être dans le futur.";
        return false;
    }

    return true;
}
</script>

</body>
</html>
