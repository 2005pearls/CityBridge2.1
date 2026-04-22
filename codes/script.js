// script.js — CityBridge

document.addEventListener('DOMContentLoaded', function () {

    function validateEmail(email) {
        if (!email) return 'Email is required.';
        if (!email.includes('@') || !email.includes('.')) return 'Email must contain @ and .';
        return null;
    }

    function validatePassword(password) {
        if (!password) return 'Password is required.';
        if (password.length < 8) return 'Password must be at least 8 characters.';
        if (!/[A-Z]/.test(password)) return 'Password must contain an uppercase letter.';
        if (!/[a-z]/.test(password)) return 'Password must contain a lowercase letter.';
        if (!/[0-9]/.test(password)) return 'Password must contain a number.';
        if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) return 'Password must contain a special character.';
        return null;
    }

    function showError(input, message) {
        let err = input.parentElement.querySelector('.error-message');
        if (!err) {
            err = document.createElement('div');
            err.className = 'error-message';
            err.style.color = '#e05c5c';
            err.style.fontSize = '0.75rem';
            err.style.marginTop = '5px';
            input.parentElement.appendChild(err);
        }
        err.textContent = message;
        err.style.display = 'block';
        input.style.borderColor = '#e05c5c';
    }

    function hideError(input) {
        const err = input.parentElement.querySelector('.error-message');
        if (err) err.style.display = 'none';
        input.style.borderColor = '';
    }

const permitForm = document.getElementById('permitForm');
 
    if (permitForm) {
      var alwaysRequired = [
            { id: 'fullname',   rule: function (v) { return v.trim() ? null : 'Full name is required.'; } },
            { id: 'idNumber',   rule: function (v) { return v.trim() ? null : 'ID number is required.'; } },
            { id: 'email',      rule: function (v) { return validateEmail(v); } },
            { id: 'phone',      rule: function (v) { return v.trim() ? null : 'Phone number is required.'; } },
            { id: 'permitType', rule: function (v) { return v ? null : 'Please select a permit type.'; } }
        ];
 
        // fields that only get validated based on the selected permit type
        var conditionalFields = {
            labor: [
                { id: 'workers',        rule: function (v) { return v ? null : 'Number of workers is required.'; } },
                { id: 'job_title',      rule: function (v) { return v.trim() ? null : 'Job title is required.'; } },
                { id: 'supervisor',     rule: function (v) { return v.trim() ? null : 'Supervisor name is required.'; } },
                { id: 'employer',       rule: function (v) { return v.trim() ? null : 'Employer name is required.'; } },
                { id: 'labor_contract', rule: function (v) { return v ? null : 'Labor contract file is required.'; } }
            ],
            equipment: [
                { id: 'equipment_type',   rule: function (v) { return v.trim() ? null : 'Equipment type is required.'; } },
                { id: 'serial_number',    rule: function (v) { return v.trim() ? null : 'Serial number is required.'; } },
                { id: 'operator',         rule: function (v) { return v.trim() ? null : 'Operator name is required.'; } },
                { id: 'operator_license', rule: function (v) { return v.trim() ? null : 'Operator license number is required.'; } },
                { id: 'equipment_docs',   rule: function (v) { return v ? null : 'Equipment documents are required.'; } }
            ],
            medical: [
                { id: 'device_name',   rule: function (v) { return v.trim() ? null : 'Device name is required.'; } },
                { id: 'manufacturer',  rule: function (v) { return v.trim() ? null : 'Manufacturer is required.'; } },
                { id: 'facility_name', rule: function (v) { return v.trim() ? null : 'Facility name is required.'; } },
                { id: 'device_cert',   rule: function (v) { return v ? null : 'Device certification document is required.'; } }
            ],
            electronic: [
                { id: 'device_type',         rule: function (v) { return v.trim() ? null : 'Device type is required.'; } },
                { id: 'device_manufacturer', rule: function (v) { return v.trim() ? null : 'Manufacturer is required.'; } },
                { id: 'device_model',        rule: function (v) { return v.trim() ? null : 'Model is required.'; } },
                { id: 'device_quantity',     rule: function (v) { return v ? null : 'Quantity is required.'; } },
                { id: 'use_type',            rule: function (v) { return v ? null : 'Please select a use type.'; } },
                { id: 'tech_spec',           rule: function (v) { return v ? null : 'Technical specification sheet is required.'; } }
            ]
        };
 
        // clear error when user starts typing or changes a field
        alwaysRequired.forEach(function (f) {
            var el = document.getElementById(f.id);
            if (!el) return;
            el.addEventListener('input',  function () { hideError(el); });
            el.addEventListener('change', function () { hideError(el); });
        });
 
        // on form submit
        permitForm.addEventListener('submit', function (e) {
            var valid = true;

            // get the selected permit type
            var type = document.getElementById('permitType').value;

            // combine always-required + the conditional fields for the selected type
            var fieldsToCheck = alwaysRequired.concat(conditionalFields[type] || []);

            // run all the rules
            fieldsToCheck.forEach(function (f) {
                var el = document.getElementById(f.id);
                if (!el) return;
                var err = f.rule(el.value);
                if (err) {
                    showError(el, err);
                    valid = false;
                } else {
                    hideError(el);
                }
            });

            if (!valid) {
                e.preventDefault();
            }
            // If valid, let the form submit normally to PHP.
        });
    }
 

 
    // LOGIN

    const loginForm = document.getElementById('loginForm');

    if (loginForm) {
        const emailInput    = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        emailInput.addEventListener('blur',  function () { const e = validateEmail(this.value);    e ? showError(emailInput, e)    : hideError(emailInput); });
        passwordInput.addEventListener('blur', function () { const e = validatePassword(this.value); e ? showError(passwordInput, e) : hideError(passwordInput); });
        emailInput.addEventListener('input',    function () { hideError(emailInput); });
        passwordInput.addEventListener('input', function () { hideError(passwordInput); });

        // Only validate on submit — let the PHP backend handle the redirect.
        loginForm.addEventListener('submit', function (e) {
            const emailErr = validateEmail(emailInput.value);
            const passErr  = validatePassword(passwordInput.value);
            emailErr ? showError(emailInput, emailErr)    : hideError(emailInput);
            passErr  ? showError(passwordInput, passErr)  : hideError(passwordInput);
            if (emailErr || passErr) {
                e.preventDefault();
            }
        });
    }


    // SIGNUP

    const signupForm = document.getElementById('signupForm');

    if (signupForm) {
        // Signup is validated server-side so the exact test-case error messages
        // flow back as styled toast banners (same pattern as login).
        // Client-side JS here does NOT block submission.
    }

}); 
// ── DOTS BACKGROUND ──
const c = document.createElement('canvas');
c.id = 'dots-canvas';
document.body.prepend(c);
const ctx = c.getContext('2d');
let dots = [];
function init() {
  c.width = innerWidth; c.height = innerHeight;
  dots = Array.from({length: 80}, () => ({
    x: Math.random() * c.width, y: Math.random() * c.height,
    vx: (Math.random()-.5)*.4,  vy: (Math.random()-.5)*.4
  }));
}
function loop() {
  ctx.clearRect(0,0,c.width,c.height);
  dots.forEach(d => {
    d.x += d.vx; d.y += d.vy;
    if(d.x<0||d.x>c.width)  d.vx*=-1;
    if(d.y<0||d.y>c.height) d.vy*=-1;
    ctx.beginPath();
    ctx.arc(d.x, d.y, 1.5, 0, Math.PI*2);
    ctx.fillStyle = 'rgba(75,174,232,0.5)';
    ctx.fill();
  });
  for(let i=0;i<dots.length;i++) for(let j=i+1;j<dots.length;j++) {
    const dx=dots[i].x-dots[j].x, dy=dots[i].y-dots[j].y;
    const dist=Math.hypot(dx,dy);
    if(dist<120) {
      ctx.beginPath();
      ctx.strokeStyle = `rgba(75,174,232,${(1-dist/120)*.15})`;
      ctx.moveTo(dots[i].x,dots[i].y); ctx.lineTo(dots[j].x,dots[j].y);
      ctx.stroke();
    }
  }
  requestAnimationFrame(loop);
}
init(); loop();
window.addEventListener('resize', init); 

 
    
// ── CITYBRIDGE TOAST MESSAGES ──
// window.showToast(message, type) where type = 'success' | 'error' | 'info'
(function () {
  function ensureContainer() {
    var c = document.getElementById('cb-toast-container');
    if (!c) {
      c = document.createElement('div');
      c.id = 'cb-toast-container';
      document.body.appendChild(c);
    }
    return c;
  }

  window.showToast = function (message, type) {
    if (!message) return;
    type = type || 'info';
    var container = ensureContainer();

    var toast = document.createElement('div');
    toast.className = 'cb-toast ' + type;

    var icon = document.createElement('span');
    icon.className = 'cb-toast-icon';
    icon.textContent = type === 'success' ? '✓' : type === 'error' ? '!' : 'i';

    var msg = document.createElement('span');
    msg.className = 'cb-toast-msg';
    msg.textContent = message;

    var close = document.createElement('button');
    close.type = 'button';
    close.className = 'cb-toast-close';
    close.setAttribute('aria-label', 'Dismiss');
    close.textContent = '×';

    toast.appendChild(icon);
    toast.appendChild(msg);
    toast.appendChild(close);
    container.appendChild(toast);

    // fade in
    requestAnimationFrame(function () { toast.classList.add('show'); });

    function dismiss() {
      toast.classList.remove('show');
      setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 300);
    }
    close.addEventListener('click', dismiss);

    // Auto-dismiss success/info after 4s; errors stay longer (6s)
    var ms = type === 'error' ? 6000 : 4000;
    setTimeout(dismiss, ms);
  };

  // On every page load, check <body data-flash="..." data-flash-type="..."> and show it
  document.addEventListener('DOMContentLoaded', function () {
    var body = document.body;
    if (!body) return;
    var flash = body.getAttribute('data-flash');
    var flashType = body.getAttribute('data-flash-type') || 'success';
    if (flash) window.showToast(flash, flashType);

    var flashError = body.getAttribute('data-flash-error');
    if (flashError) window.showToast(flashError, 'error');
  });
})();
