
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  .overlay {
    min-height: 500px;
    background: rgba(17,24,39,0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
  }
  .modal {
    background: #ffffff;
    border-radius: 22px;
    border: 1.5px solid #e5e7eb;
    width: 100%;
    max-width: 440px;
    padding: 32px;
    position: relative;
  }
  .modal-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
  }
  .modal-icon {
    width: 40px; height: 40px;
    border-radius: 11px;
    background: #e8f7f4;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .modal-icon svg { width: 18px; height: 18px; stroke: #2baa8f; fill: none; stroke-width: 2.2; }
  .modal-title { font-size: 1.05rem; font-weight: 800; color: #111827; }
  .modal-subtitle { font-size: .78rem; color: #6b7280; margin-top: 2px; }
  .close-btn {
    position: absolute; top: 20px; right: 20px;
    width: 30px; height: 30px;
    border-radius: 50%;
    border: 1.5px solid #e5e7eb;
    background: #fff;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: #6b7280;
    transition: all .18s;
  }
  .close-btn:hover { background: #f4f6f8; border-color: #d1d5db; }
  .close-btn svg { width: 14px; height: 14px; stroke: currentColor; stroke-width: 2.5; fill: none; }
  .field-group { margin-bottom: 16px; }
  .field-group label {
    display: block;
    font-size: .775rem;
    font-weight: 700;
    color: #374151;
    margin-bottom: 6px;
    letter-spacing: .02em;
  }
  .field-wrap {
    position: relative;
    display: flex;
    align-items: center;
  }
  .field-icon {
    position: absolute;
    left: 13px;
    width: 15px; height: 15px;
    stroke: #9ca3af; fill: none; stroke-width: 2.1;
    pointer-events: none;
    flex-shrink: 0;
  }
  .field-wrap input {
    width: 100%;
    height: 42px;
    padding: 0 42px 0 40px;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    font-family: inherit;
    font-size: .875rem;
    color: #111827;
    background: #f9fafb;
    outline: none;
    transition: border-color .18s, background .18s;
  }
  .field-wrap input:focus {
    border-color: #2baa8f;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(43,170,143,.12);
  }
  .toggle-eye {
    position: absolute; right: 12px;
    background: none; border: none; cursor: pointer;
    padding: 4px; color: #9ca3af;
    display: flex; align-items: center;
    transition: color .18s;
  }
  .toggle-eye:hover { color: #2baa8f; }
  .toggle-eye svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2.1; }
  .strength-bar {
    margin-top: 8px;
    display: flex; gap: 5px;
  }
  .strength-seg {
    height: 4px;
    flex: 1;
    border-radius: 4px;
    background: #e5e7eb;
    transition: background .3s;
  }
  .strength-label {
    font-size: .72rem;
    margin-top: 5px;
    color: #6b7280;
    font-weight: 600;
    min-height: 16px;
  }
  .match-hint {
    font-size: .72rem;
    margin-top: 5px;
    font-weight: 600;
    min-height: 16px;
    display: flex; align-items: center; gap: 4px;
  }
  .match-hint svg { width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2.1; }
  .divider { height: 1px; background: #f3f4f6; margin: 20px 0; }
  .btn-row { display: flex; gap: 10px; }
  .btn-cancel {
    flex: 1;
    height: 42px;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    background: #fff;
    font-family: inherit;
    font-size: .85rem; font-weight: 700;
    color: #6b7280;
    cursor: pointer;
    transition: all .18s;
  }
  .btn-cancel:hover { background: #f4f6f8; border-color: #d1d5db; }
  .btn-save {
    flex: 1.6;
    height: 42px;
    border: none;
    border-radius: 12px;
    background: #2baa8f;
    font-family: inherit;
    font-size: .85rem; font-weight: 700;
    color: #fff;
    cursor: pointer;
    transition: background .18s, transform .12s;
    display: flex; align-items: center; justify-content: center; gap: 7px;
  }
  .btn-save:hover { background: #1f8a73; }
  .btn-save:active { transform: scale(.98); }
  .btn-save svg { width: 15px; height: 15px; stroke: #fff; fill: none; stroke-width: 2.5; }
  .btn-save:disabled { background: #a7d9ce; cursor: not-allowed; }
  .success-state {
    display: none;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 16px 0 4px;
  }
  .success-icon-wrap {
    width: 56px; height: 56px;
    border-radius: 50%;
    background: #e8f7f4;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 14px;
  }
  .success-icon-wrap svg { width: 26px; height: 26px; stroke: #2baa8f; fill: none; stroke-width: 2.5; }
  .success-title { font-size: 1rem; font-weight: 800; color: #111827; margin-bottom: 5px; }
  .success-msg { font-size: .825rem; color: #6b7280; line-height: 1.5; }
</style>

<div class="overlay">
  <div class="modal" id="modal">
    <button class="close-btn" onclick="window.location.href='Profil.php'">
      <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>

    <div id="formView">
      <div class="modal-header">
        <div class="modal-icon">
          <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        </div>
        <div>
          <div class="modal-title">Changer le mot de passe</div>
          <div class="modal-subtitle">Votre nouveau mot de passe doit être différent du précédent</div>
        </div>
      </div>

      <div class="field-group">
        <label>Mot de passe actuel</label>
        <div class="field-wrap">
          <svg class="field-icon" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          <input type="password" id="current" placeholder="••••••••" oninput="validate()">
          <button class="toggle-eye" onclick="toggleVis('current', this)">
            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>


      <div id="currentError" style="font-size:12px;color:red;margin-top:4px;"></div>

      <div class="field-group">
        <label>Nouveau mot de passe</label>
        <div class="field-wrap">
          <svg class="field-icon" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          <input type="password" id="newpwd" placeholder="••••••••" oninput="updateStrength(); validate()">
          <button class="toggle-eye" onclick="toggleVis('newpwd', this)">
            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <div class="strength-bar">
          <div class="strength-seg" id="s1"></div>
          <div class="strength-seg" id="s2"></div>
          <div class="strength-seg" id="s3"></div>
          <div class="strength-seg" id="s4"></div>
        </div>
        <div class="strength-label" id="strengthLabel"></div>
      </div>

      <div class="field-group">
        <label>Confirmer le nouveau mot de passe</label>
        <div class="field-wrap">
          <svg class="field-icon" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          <input type="password" id="confirm" placeholder="••••••••" oninput="checkMatch(); validate()">
          <button class="toggle-eye" onclick="toggleVis('confirm', this)">
            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <div class="match-hint" id="matchHint"></div>
      </div>

      <div class="divider"></div>

      <div class="btn-row">
        <button class="btn-cancel" onclick="window.location.href='Profil.php'">Annuler</button>
        <button type="button" class="btn-save" id="saveBtn" onclick="save()" disabled>
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          Enregistrer
        </button>
      </div>
    </div>

    <div class="success-state" id="successView">
      <div class="success-icon-wrap">
        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <div class="success-title">Mot de passe mis à jour !</div>
      <div class="success-msg">Votre mot de passe a été modifié avec succès.<br>Vous serez déconnecté des autres appareils.</div>
    </div>
  </div>
</div>

<script>
  const colors = { weak: '#ef4444', fair: '#f59e0b', good: '#2baa8f', strong: '#1f8a73' };

  function getStrength(p) {
    let score = 0;
    if (p.length >= 8) score++;
    if (/[A-Z]/.test(p)) score++;
    if (/[0-9]/.test(p)) score++;
    if (/[^A-Za-z0-9]/.test(p)) score++;
    return score;
  }

  function updateStrength() {
    const p = document.getElementById('newpwd').value;
    const s = getStrength(p);
    const segs = [document.getElementById('s1'), document.getElementById('s2'), document.getElementById('s3'), document.getElementById('s4')];
    const labels = ['', 'Faible', 'Moyen', 'Bon', 'Fort'];
    const clrs = ['#e5e7eb', colors.weak, colors.fair, colors.good, colors.strong];
    segs.forEach((seg, i) => { seg.style.background = i < s ? clrs[s] : '#e5e7eb'; });
    document.getElementById('strengthLabel').textContent = p.length ? labels[s] : '';
    document.getElementById('strengthLabel').style.color = clrs[s];
  }

  function checkMatch() {
    const n = document.getElementById('newpwd').value;
    const c = document.getElementById('confirm').value;
    const el = document.getElementById('matchHint');
    if (!c.length) { el.innerHTML = ''; return; }
    if (n === c) {
      el.innerHTML = '<svg viewBox="0 0 24 24" style="stroke:#10b981"><polyline points="20 6 9 17 4 12"/></svg><span style="color:#10b981">Les mots de passe correspondent</span>';
    } else {
      el.innerHTML = '<svg viewBox="0 0 24 24" style="stroke:#ef4444"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg><span style="color:#ef4444">Les mots de passe ne correspondent pas</span>';
    }
  }

  function isStrongPassword(p) {
  return (
    p.length >= 8 &&
    /[A-Z]/.test(p) &&
    /[0-9]/.test(p)
  );
}

function validate() {
  const cur = document.getElementById('current').value;
  const n = document.getElementById('newpwd').value;
  const c = document.getElementById('confirm').value;

  let ok = true;

  if (cur.length === 0) ok = false;
  if (!isStrongPassword(n)) ok = false;
  if (n !== c) ok = false;

  document.getElementById('saveBtn').disabled = !ok;
}

  function toggleVis(id, btn) {
    const inp = document.getElementById(id);
    const isPass = inp.type === 'password';
    inp.type = isPass ? 'text' : 'password';
    const svg = btn.querySelector('svg');
    svg.innerHTML = isPass
      ? '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>'
      : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  }

function save() {
  const formData = new FormData();

  formData.append("current", document.getElementById("current").value);
  formData.append("newpwd", document.getElementById("newpwd").value);
  formData.append("change_password", "1");

  console.log("SEND REQUEST...");

  fetch("../../controller/UserC.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    console.log("SERVER RESPONSE:", data);

    alert(data); // 🔥 مهم باش تشوف شنو يرجع السيرفر

    /*if (data.trim() === "wrong_password") {
      document.getElementById("currentError").innerText =
        "❌ Mot de passe incorrect";
    }*/

    if (data.trim() === "success") {
      window.location.href = "Profil.php";
    }
  })
  .catch(err => {
    console.log("FETCH ERROR:", err);
  });
}


  function closeModal() {
    document.getElementById('formView').style.display = 'block';
    document.getElementById('successView').style.display = 'none';
    ['current','newpwd','confirm'].forEach(id => document.getElementById(id).value = '');
    updateStrength(); checkMatch(); validate();
    document.getElementById('matchHint').innerHTML = '';
  }


</script>


