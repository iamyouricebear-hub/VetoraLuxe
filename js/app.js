let currentPage = 'dashboard';
let mapInstance = null;

// ============================================================
// TOAST
// ============================================================
function showToast(msg, type = 'success') {
  const container = document.getElementById('toast-container');
  if (!container) return;
  const d = document.createElement('div');
  d.className = `toast ${type}`;
  d.innerHTML = `<span>${type === 'error' ? '⚠️' : '✅'}</span> <div>${msg}</div>`;
  container.appendChild(d);
  requestAnimationFrame(() => d.classList.add('show'));
  setTimeout(() => {
    d.style.transition = '0.3s';
    d.style.opacity = '0';
    d.style.transform = 'translateX(120%)';
    setTimeout(() => d.remove(), 300);
  }, 3000);
}

// ============================================================
// AUTH
// ============================================================
async function doLogin() {
  const email    = document.getElementById('login-email').value;
  const password = document.getElementById('login-pass').value;
  const res  = await fetch('api/auth.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  const data = await res.json();
  if (data.status === 'success') {
    window.location.reload();
  } else {
    showToast(data.message || 'Login failed', 'error');
  }
}

async function doLogout() {
  await fetch('api/auth.php', { method: 'DELETE' });
  window.location.reload();
}

// ============================================================
// NAVIGATION
// ============================================================
function nav(page, el = null) {
  currentPage = page;
  document.querySelectorAll('.page-content').forEach(p => p.classList.remove('active'));
  const pageEl = document.getElementById('page-' + page);
  if (pageEl) pageEl.classList.add('active');
  if (el) {
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    el.classList.add('active');
  }
  initPageData(page);
}

function initPageData(page) {
  if (page === 'dashboard')    loadDashboard();
  if (page === 'patients')     loadPatients();
  if (page === 'doctors')      loadDoctors();
  if (page === 'appointments') loadApts();
  if (page === 'pharmacy')     loadDrugs();
  if (page === 'finance')      loadFinance();
  if (page === 'records')      loadRecordsPatList();
  if (page === 'maps')         setTimeout(initMap, 200);
}

// ============================================================
// DASHBOARD
// FIX: greeting by time, today-only queue, live activity from real data
// ============================================================
function getGreeting() {
  const h = new Date().getHours();
  if (h < 12) return 'Good Morning';
  if (h < 17) return 'Good Afternoon';
  return 'Good Evening';
}

async function loadDashboard() {
  // FIX: update greeting dynamically
  const greetEl = document.getElementById('dash-greeting');
  if (greetEl) greetEl.textContent = getGreeting() + ', ';

  try {
    const [rPat, rDoc, rApt, rTrans] = await Promise.all([
      fetch('api/patients.php'),
      fetch('api/doctors.php'),
      fetch('api/appointments.php'),
      fetch('api/transactions.php')
    ]);
    const p = await rPat.json();
    const d = await rDoc.json();
    const a = await rApt.json();
    const t = await rTrans.json();

    // Stats
    document.getElementById('dash-stats').innerHTML = `
      <div class="stat-card">
        <div class="stat-title">👥<br>Patients</div>
        <div class="stat-number">${p.data.length}</div>
      </div>
      <div class="stat-card">
        <div class="stat-title">👨‍⚕️<br>Doctors</div>
        <div class="stat-number">${d.data.length}</div>
      </div>
      <div class="stat-card">
        <div class="stat-title">🗓️<br>Appointments</div>
        <div class="stat-number">${a.data.length}</div>
      </div>
      <div class="stat-card">
        <div class="stat-title">💰<br>Revenue</div>
        <div class="stat-number">Rp${Number(t.total || 0).toLocaleString('id-ID')}</div>
      </div>
    `;

    // FIX: Filter appointments for today only
    const todayStr = new Date().toISOString().split('T')[0];
    const todayApts = a.data.filter(apt => apt.apt_date === todayStr);
    const displayApts = todayApts.length > 0 ? todayApts : a.data.slice(0, 5);

    let qH = '';
    displayApts.slice(0, 5).forEach(apt => {
      const sc = apt.status.toLowerCase().replace(/\s+/g, '-');
      const petName = apt.patient_name ? apt.patient_name.split('|')[0].trim() : 'Unknown';
      qH += `
        <div class="patient-flow-row">
          <div class="pet-identity">
            <div class="pet-icon">🐾</div>
            <div>
              <div class="pet-name">${petName}</div>
              <div class="pet-species"><i data-feather="user" style="width:12px;"></i> ${apt.doctor_name || '-'}</div>
            </div>
          </div>
          <div class="flow-steps">
            <div class="step active-step"><span class="step-dot"></span>${apt.apt_time}</div>
            <span class="flow-arrow">→</span>
            <div class="step"><span class="step-dot"></span>Checkup</div>
          </div>
          <div class="status-chip chip-${sc}">${apt.status}</div>
        </div>`;
    });
    document.getElementById('dash-queue-list').innerHTML = qH ||
      '<div style="padding:40px; text-align:center; color:#BFA2DB;">No appointments today 🐾</div>';

    // FIX: Live activity from real transaction + appointment data (dynamic)
    const feedEl = document.getElementById('recent-activity-feed');
    if (feedEl) {
      let feedH = '';
      const colors = ['#E8A0BF', '#BFA2DB', '#D4A373'];
      // Combine latest transactions and appointments for the feed
      const recentTrans = (t.data || []).slice(0, 2);
      const recentApts  = (a.data || []).slice(0, 2);

      recentTrans.forEach((tx, i) => {
        feedH += `
          <div class="timeline-item">
            <div class="timeline-marker" style="background:${colors[i % colors.length]}; box-shadow:0 0 0 4px ${colors[i % colors.length]}30;"></div>
            <div class="timeline-content">
              <p>${tx.service_name}</p>
              <span class="timeline-time">${tx.patient_name || 'Guest'} · ${tx.transaction_date}</span>
            </div>
          </div>`;
      });
      recentApts.forEach((apt, i) => {
        feedH += `
          <div class="timeline-item">
            <div class="timeline-marker" style="background:${colors[(i + 2) % colors.length]}; box-shadow:0 0 0 4px ${colors[(i+2)%colors.length]}30;"></div>
            <div class="timeline-content">
              <p>Appointment: ${apt.patient_name || '-'}</p>
              <span class="timeline-time">${apt.doctor_name || '-'} · ${apt.apt_date}</span>
            </div>
          </div>`;
      });
      feedEl.innerHTML = feedH || '<p style="color:#BFA2DB; padding:12px;">No recent activity</p>';
    }

    // FIX: Vet availability from real doctor data
    const vetEl = document.getElementById('vet-availability-list');
    if (vetEl && d.data) {
      const vetH = d.data.slice(0, 3).map(doc => {
        // Doctors with appointments today are "In Checkup", others "Available"
        const busyToday = a.data.some(apt => apt.doctor_id == doc.id && apt.apt_date === todayStr);
        const statusLabel = busyToday ? 'In Checkup' : 'Available';
        const statusClass = busyToday ? 'yellow-status' : 'green-status';
        const dotClass    = busyToday ? 'yellow-dot'   : 'green-dot';
        return `
          <div class="vet-availability">
            <span class="vet-name"><i data-feather="user" style="width:16px;"></i> ${doc.name}</span>
            <div class="vet-status ${statusClass}"><div class="status-dot ${dotClass}"></div> ${statusLabel}</div>
          </div>`;
      }).join('');
      vetEl.innerHTML = vetH;
    }

    if (window.feather) feather.replace();
  } catch (e) {
    console.error('Dashboard load error:', e);
  }
}

// ============================================================
// PATIENTS
// FIX: Correct column display (blood_type shown as Species/Breed label)
// ============================================================
let appPatients = [];
async function loadPatients() {
  const res  = await fetch('api/patients.php');
  const data = await res.json();
  appPatients = data.data;
  const search = (document.getElementById('srch-pat')?.value || '').toLowerCase();
  let H = '';
  appPatients.forEach(x => {
    if (!x.name.toLowerCase().includes(search) && !(x.phone || '').includes(search)) return;
    // FIX: Show name+gender+dob correctly; show blood_type in correct column; show addr correctly
    H += `<tr class="hover">
      <td>
        <div style="display:flex; align-items:center; gap:14px;">
          <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(x.name)}&background=random" style="width:44px;height:44px;border-radius:50%;box-shadow:0 2px 4px rgba(0,0,0,0.1);border:2px solid white;">
          <div>
            <strong style="font-size:0.95rem; color:#4e3643;">${x.name}</strong>
            <div style="font-size:0.8rem; color:#7E6272;">${x.gender} · Born ${x.dob || '-'}</div>
          </div>
        </div>
      </td>
      <td>${x.phone || '-'}</td>
      <td>${x.blood_type || '-'} · ${x.allergy ? '⚠️ ' + x.allergy : 'No allergy'}</td>
      <td>${x.addr || '-'}</td>
      <td>
        <div style="display:flex;gap:6px;">
          <button class="btn-icon" onclick="editPatient(${x.id})" title="Edit">✏️</button>
          <button class="btn-icon" onclick="deletePatient(${x.id})" title="Delete">🗑️</button>
        </div>
      </td>
    </tr>`;
  });
  document.getElementById('patients-list').innerHTML = H ||
    '<tr><td colspan="5" style="text-align:center;padding:40px;color:#BFA2DB;">No patients found 🐾</td></tr>';
}

function editPatient(id) {
  const p = appPatients.find(x => x.id == id);
  if (!p) return;
  document.getElementById('p-id').value     = p.id;
  document.getElementById('p-name').value   = p.name;
  document.getElementById('p-phone').value  = p.phone;
  document.getElementById('p-addr').value   = p.addr;
  document.getElementById('p-gender').value = p.gender;
  document.getElementById('p-blood').value  = p.blood_type;
  document.getElementById('p-dob').value    = p.dob;
  document.getElementById('p-allergy').value = p.allergy;
  openModal('mod-patient');
}

async function savePatient() {
  const id = document.getElementById('p-id').value;
  const payload = {
    id,
    name:       document.getElementById('p-name').value,
    phone:      document.getElementById('p-phone').value,
    addr:       document.getElementById('p-addr').value,
    gender:     document.getElementById('p-gender').value,
    blood_type: document.getElementById('p-blood').value,
    dob:        document.getElementById('p-dob').value,
    allergy:    document.getElementById('p-allergy').value
  };
  await fetch('api/patients.php', {
    method: id ? 'PUT' : 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  document.getElementById('p-id').value   = '';
  document.getElementById('p-name').value = '';
  closeModal('mod-patient');
  showToast(id ? 'Patient Updated!' : 'Patient Added!');
  loadPatients();
}

async function deletePatient(id) {
  showConfirm('Remove this patient? All related appointments and records will also be deleted.', async () => {
    await fetch('api/patients.php?id=' + id, { method: 'DELETE' });
    loadPatients();
    showToast('Patient deleted');
  });
}

// ============================================================
// DOCTORS
// ============================================================
let appDoctors = [];
async function loadDoctors() {
  const res  = await fetch('api/doctors.php');
  const data = await res.json();
  appDoctors = data.data;
  const search = (document.getElementById('srch-doc')?.value || '').toLowerCase();
  let H = '';
  appDoctors.forEach(x => {
    if (!x.name.toLowerCase().includes(search)) return;
    H += `<tr class="hover">
      <td>
        <div style="display:flex; align-items:center; gap:12px;">
          <div style="width:40px;height:40px;border-radius:50%;background:#FCE9EF;display:flex;align-items:center;justify-content:center;border:2px solid white;box-shadow:0 2px 6px rgba(232,160,191,0.2);color:#A98A9A;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
          </div>
          <strong>${x.name}</strong>
        </div>
      </td>
      <td>${x.specialization || '-'}</td>
      <td>${x.practice_hours || '-'}</td>
      <td><span class="badge Active">${x.status || 'Active'}</span></td>
      <td>
        <div style="display:flex;gap:6px;">
          <button class="btn-icon" onclick="editDoctor(${x.id})">✏️</button>
          <button class="btn-icon" onclick="deleteDoctor(${x.id})">🗑️</button>
        </div>
      </td>
    </tr>`;
  });
  document.getElementById('doctors-list').innerHTML = H ||
    '<tr><td colspan="5" style="text-align:center;padding:40px;color:#BFA2DB;">No doctors found</td></tr>';
}

function editDoctor(id) {
  const d = appDoctors.find(x => x.id == id);
  if (!d) return;
  document.getElementById('d-id').value    = d.id;
  document.getElementById('d-name').value  = d.name;
  document.getElementById('d-spec').value  = d.specialization;
  document.getElementById('d-hours').value = d.practice_hours;
  openModal('mod-doctor');
}

async function saveDoctor() {
  const id = document.getElementById('d-id').value;
  const payload = {
    id,
    name:           document.getElementById('d-name').value,
    specialization: document.getElementById('d-spec').value,
    practice_hours: document.getElementById('d-hours').value,
    status: 'Active'
  };
  await fetch('api/doctors.php', {
    method: id ? 'PUT' : 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  document.getElementById('d-id').value   = '';
  document.getElementById('d-name').value = '';
  closeModal('mod-doctor');
  showToast(id ? 'Doctor Updated!' : 'Doctor Added!');
  loadDoctors();
}

async function deleteDoctor(id) {
  showConfirm('Remove this doctor?', async () => {
    await fetch('api/doctors.php?id=' + id, { method: 'DELETE' });
    loadDoctors();
    showToast('Doctor removed');
  });
}

// ============================================================
// APPOINTMENTS
// ============================================================
let aptPatients = [];
let aptDoctors  = [];
let appApts     = [];

async function populateAptSelects() {
  const [rPat, rDoc] = await Promise.all([fetch('api/patients.php'), fetch('api/doctors.php')]);
  const p = await rPat.json();
  const d = await rDoc.json();
  aptPatients = p.data;
  aptDoctors  = d.data;

  const patList = document.getElementById('pat-list');
  patList.innerHTML = '';
  aptPatients.forEach(x => patList.appendChild(new Option(x.name)));

  const docList = document.getElementById('doc-list');
  docList.innerHTML = '';
  aptDoctors.forEach(x => docList.appendChild(new Option(x.name)));
}

async function loadApts() {
  const res  = await fetch('api/appointments.php');
  const data = await res.json();
  appApts = data.data;
  let H = '';
  appApts.forEach(x => {
    const sc = x.status.toLowerCase().replace(/\s+/g, '-');
    H += `<tr class="hover">
      <td><strong>${x.apt_date}</strong><br><span style="color:#A98A9A;font-size:13px;">${x.apt_time}</span></td>
      <td>${x.patient_name || '-'}</td>
      <td>${x.doctor_name  || '-'}</td>
      <td><span class="status-chip chip-${sc}">${x.status}</span></td>
      <td>
        <div style="display:flex;gap:6px;">
          <button class="btn-icon" onclick="editApt(${x.id})">✏️</button>
          <button class="btn-icon" onclick="deleteApt(${x.id})">🗑️</button>
        </div>
      </td>
    </tr>`;
  });
  document.getElementById('apts-list').innerHTML = H ||
    '<tr><td colspan="5" style="text-align:center;padding:40px;color:#BFA2DB;">No appointments.</td></tr>';
}

function editApt(id) {
  const a = appApts.find(x => x.id == id);
  if (!a) return;
  document.getElementById('a-id').value     = a.id;
  document.getElementById('a-pat').value    = a.patient_name;
  document.getElementById('a-doc').value    = a.doctor_name;
  document.getElementById('a-date').value   = a.apt_date;
  document.getElementById('a-time').value   = a.apt_time;
  document.getElementById('a-status').value = a.status;
  openModal('mod-apt');
}

async function saveApt() {
  const id    = document.getElementById('a-id').value;
  const pName = document.getElementById('a-pat').value;
  const dName = document.getElementById('a-doc').value;

  // Resolve or auto-create patient
  let pMatch = aptPatients.find(x => x.name.toLowerCase() === pName.toLowerCase());
  let pat_id = pMatch ? pMatch.id : null;
  if (!pat_id) {
    const r   = await fetch('api/patients.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ name: pName, phone: '', addr: 'Auto-created' }) });
    pat_id = (await r.json()).id;
  }

  // Resolve doctor
  let dMatch = aptDoctors.find(x => x.name.toLowerCase() === dName.toLowerCase());
  let doc_id = dMatch ? dMatch.id : null;

  const oldData   = appApts.find(x => x.id == id);
  const oldStatus = oldData ? oldData.status.toLowerCase() : '';
  const newStatus = document.getElementById('a-status').value;

  const payload = { id, patient_id: pat_id, doctor_id: doc_id, apt_date: document.getElementById('a-date').value, apt_time: document.getElementById('a-time').value, status: newStatus };
  await fetch('api/appointments.php', { method: id ? 'PUT' : 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });

  // Auto-create transaction when status changes to Recovering
  if (newStatus.toLowerCase() === 'recovering' && oldStatus !== 'recovering') {
    const check = await (await fetch('api/transactions.php')).json();
    const exists = check.data.find(tx => tx.patient_name === pName && tx.service_name === 'Vet Checkup');
    if (!exists && pat_id) {
      await fetch('api/transactions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ patient_id: pat_id, service_name: 'Vet Checkup', amount: 50000 })
      });
    }
  }

  document.getElementById('a-id').value = '';
  closeModal('mod-apt');
  showToast(id ? 'Appointment Updated!' : 'Appointment Created!');
  loadApts();
}

async function deleteApt(id) {
  showConfirm('Cancel this appointment?', async () => {
    await fetch('api/appointments.php?id=' + id, { method: 'DELETE' });
    loadApts();
    showToast('Appointment removed');
  });
}

// ============================================================
// DRUGS
// FIX: drug-name is now a free-text input (not hardcoded dropdown)
// ============================================================
let appDrugs = [];
async function loadDrugs() {
  const res  = await fetch('api/drugs.php');
  const data = await res.json();
  appDrugs = data.data;
  const search = (document.getElementById('srch-drug')?.value || '').toLowerCase();
  let H = '';
  data.data.forEach(x => {
    if (!x.name.toLowerCase().includes(search)) return;
    const stockColor = x.stock < 10 ? '#D33' : x.stock < 50 ? '#C77700' : '#2E7D32';
    const stockBg    = x.stock < 10 ? '#FFE5E5' : x.stock < 50 ? '#FFF6E0' : '#E8F8F0';
    H += `<tr>
      <td>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:36px;height:36px;border-radius:10px;background:#FFE4EC;display:flex;align-items:center;justify-content:center;font-size:18px;">💊</div>
          <strong>${x.name}</strong>
        </div>
      </td>
      <td><span style="padding:6px 12px;border-radius:12px;background:${stockBg};color:${stockColor};font-weight:600;">${x.stock} units</span></td>
      <td><div style="font-weight:600;color:#4A3F55;background:#F5ECFF;padding:6px 10px;border-radius:10px;display:inline-block;">Rp ${parseInt(x.price || 0).toLocaleString('id-ID')}</div></td>
      <td>
        <div style="display:flex;gap:6px;">
          <button class="btn-icon" onclick="editDrug(${x.id})">✏️</button>
          <button class="btn-icon" onclick="delDrug(${x.id})">🗑️</button>
        </div>
      </td>
    </tr>`;
  });
  document.getElementById('drugs-list').innerHTML = H ||
    '<tr><td colspan="4" style="text-align:center;padding:40px;color:#BFA2DB;">💊 No medicines found</td></tr>';
}

function editDrug(id) {
  const d = appDrugs.find(x => x.id == id);
  if (!d) return;
  let inp = document.getElementById('drug-id');
  if (!inp) {
    inp = document.createElement('input');
    inp.type = 'hidden';
    inp.id   = 'drug-id';
    document.querySelector('#mod-drug form').appendChild(inp);
  }
  inp.value = d.id;
  // FIX: set the text input (not dropdown) for editing
  document.getElementById('drug-name-input').value  = d.name;
  document.getElementById('drug-stock').value = d.stock;
  document.getElementById('drug-price').value = d.price;
  openModal('mod-drug');
}

async function saveDrug() {
  const id    = document.getElementById('drug-id')?.value || '';
  const payload = {
    id,
    name:  document.getElementById('drug-name-input').value,
    stock: document.getElementById('drug-stock').value,
    price: document.getElementById('drug-price').value
  };
  await fetch('api/drugs.php', {
    method: id ? 'PUT' : 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  if (document.getElementById('drug-id')) document.getElementById('drug-id').value = '';
  document.getElementById('drug-name-input').value = '';
  closeModal('mod-drug');
  showToast(id ? 'Stock Updated!' : 'Stock Added!');
  loadDrugs();
}

async function delDrug(id) {
  showConfirm('Remove this medicine?', async () => {
    await fetch('api/drugs.php?id=' + id, { method: 'DELETE' });
    loadDrugs();
    showToast('Medicine removed');
  });
}

// ============================================================
// RECORDS
// FIX: replaced window.prompt() with proper modal form
// ============================================================
let currentRecPat   = null;
let currentRecName  = '';

async function loadRecordsPatList() {
  const res = await fetch('api/patients.php');
  const p   = await res.json();
  let H = '';
  p.data.forEach(x => {
    const cleanName = x.name.replace(/[\u{1F000}-\u{1FFFF}\u{2600}-\u{27BF}]/gu, '').trim();
    H += `<div style="display:flex;align-items:center;gap:14px;padding:12px 16px;background:rgba(255,255,255,0.6);border-radius:18px;cursor:pointer;border:2px solid transparent;transition:0.2s;box-shadow:0 4px 6px rgba(191,162,171,0.05);"
            onclick="loadRecordPatDetail(${x.id}, '${cleanName.replace(/'/g, "\\'")}')"
            onmouseover="this.style.background='#fff';this.style.borderColor='#F6D6E0';this.style.transform='translateY(-2px)';"
            onmouseout="this.style.background='rgba(255,255,255,0.6)';this.style.borderColor='transparent';this.style.transform='translateY(0)';">
            <img style="width:44px;height:44px;border-radius:50%;box-shadow:0 2px 4px rgba(0,0,0,0.1);border:2px solid white;"
              src="https://ui-avatars.com/api/?name=${encodeURIComponent(cleanName)}&background=random&color=fff">
            <strong style="color:#4e3643;font-size:0.95rem;">${cleanName}</strong>
          </div>`;
  });
  document.getElementById('record-pat-list').innerHTML = H;
}

async function loadRecordPatDetail(id, name) {
  currentRecPat  = id;
  currentRecName = name;
  document.getElementById('rec-pat-title').innerText = 'History: ' + name;
  document.getElementById('btn-add-rec').style.display = 'inline-flex';

  const res = await fetch('api/records.php?patient_id=' + id);
  const r   = await res.json();
  let H = '';
  r.data.forEach(x => {
    H += `<div class="patient-flow-row" style="display:flex;flex-direction:column;align-items:flex-start;gap:12px;padding:20px;margin-bottom:16px;cursor:default;">
        <div style="display:flex;justify-content:space-between;align-items:center;width:100%;border-bottom:1px dashed #FFE7EF;padding-bottom:12px;">
          <div style="font-weight:700;color:#4a3641;font-size:1rem;display:flex;align-items:center;gap:8px;">
            <i data-feather="calendar" style="width:16px;height:16px;color:#E8A0BF;"></i> ${x.record_date}
          </div>
          <div class="status-chip chip-recovering" style="margin:0;">Done / Recovering</div>
        </div>
        <div style="width:100%;display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px;margin-top:4px;">
          <div>
            <div style="font-size:0.8rem;color:#9B7B8A;text-transform:uppercase;letter-spacing:0.02em;font-weight:600;margin-bottom:4px;">Pet Name</div>
            <div style="font-weight:600;color:#3A2E39;">🐾 ${name}</div>
          </div>
          <div>
            <div style="font-size:0.8rem;color:#9B7B8A;text-transform:uppercase;letter-spacing:0.02em;font-weight:600;margin-bottom:4px;">Animal Doctor</div>
            <div style="font-weight:600;color:#3A2E39;">👤 ${x.doctor_name || '-'}</div>
          </div>
          <div>
            <div style="font-size:0.8rem;color:#9B7B8A;text-transform:uppercase;letter-spacing:0.02em;font-weight:600;margin-bottom:4px;">Diagnosis</div>
            <div style="font-weight:600;color:#3A2E39;">${x.diagnosis || '-'}</div>
          </div>
          <div>
            <div style="font-size:0.8rem;color:#9B7B8A;text-transform:uppercase;letter-spacing:0.02em;font-weight:600;margin-bottom:4px;">Therapy / Rx</div>
            <div style="font-weight:500;color:#5E4353;">${x.therapy || '-'}</div>
          </div>
        </div>
      </div>`;
  });
  document.getElementById('record-detail-list').innerHTML = H ||
    '<div style="color:#BFA2DB;text-align:center;padding:60px 40px;">No medical records found for this pet 🐾</div>';
  if (window.feather) feather.replace();
}

// FIX: openRecordModal now opens a proper modal instead of prompt()
function openRecordModal() {
  if (!currentRecPat) return;
  // Populate doctor select in record modal
  populateRecordDoctors();
  document.getElementById('rec-pat-label').textContent = 'Patient: ' + currentRecName;
  openModal('mod-record');
}

async function populateRecordDoctors() {
  if (appDoctors.length === 0) {
    const res = await fetch('api/doctors.php');
    appDoctors = (await res.json()).data;
  }
  const sel = document.getElementById('rec-doctor-id');
  sel.innerHTML = '<option value="">Select Doctor</option>';
  appDoctors.forEach(d => {
    sel.innerHTML += `<option value="${d.id}">${d.name}</option>`;
  });
}

async function saveRecord() {
  const doc_id    = document.getElementById('rec-doctor-id').value;
  const diagnosis = document.getElementById('rec-diagnosis').value;
  const therapy   = document.getElementById('rec-therapy').value;

  if (!doc_id || !diagnosis) {
    showToast('Please fill in doctor and diagnosis', 'error');
    return;
  }

  await fetch('api/records.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ patient_id: currentRecPat, doctor_id: doc_id, diagnosis, therapy })
  });

  document.getElementById('rec-doctor-id').value = '';
  document.getElementById('rec-diagnosis').value = '';
  document.getElementById('rec-therapy').value   = '';
  closeModal('mod-record');
  showToast('Medical record saved!');
  loadRecordPatDetail(currentRecPat, currentRecName);
}

// ============================================================
// FINANCE
// FIX: Added search filter
// ============================================================
let appTransactions = [];
async function loadFinance() {
  const res = await fetch('api/transactions.php');
  const t   = await res.json();
  appTransactions = t.data || [];

  const totalEl = document.getElementById('finance-total');
  const listEl  = document.getElementById('trans-list');
  if (!totalEl || !listEl) return;

  totalEl.innerText = 'Rp ' + parseInt(t.total || 0).toLocaleString('id-ID');

  const search = (document.getElementById('srch-trans')?.value || '').toLowerCase();
  let H = '';
  appTransactions.forEach(x => {
    if (search && !(x.patient_name || '').toLowerCase().includes(search) && !(x.service_name || '').toLowerCase().includes(search)) return;
    H += `<tr>
      <td>${x.transaction_date}</td>
      <td>${x.patient_name || 'Guest'}</td>
      <td>${x.service_name}</td>
      <td>Rp ${parseInt(x.amount).toLocaleString('id-ID')}</td>
      <td>
        <div style="display:flex;gap:6px;">
          <a href="invoice.php?id=${x.id}" target="_blank" class="btn-icon" title="Invoice" style="text-decoration:none;">📄</a>
          <button class="btn-icon" onclick="editTransaction(${x.id})">✏️</button>
          <button class="btn-icon" onclick="deleteTransaction(${x.id})">🗑️</button>
        </div>
      </td>
    </tr>`;
  });
  listEl.innerHTML = H || '<tr><td colspan="5" style="text-align:center;padding:40px;color:#BFA2DB;">No transactions yet</td></tr>';
}

function editTransaction(id) {
  const t = appTransactions.find(x => x.id == id);
  if (!t) return;
  document.getElementById('t-id').value      = t.id;
  document.getElementById('t-pat').value     = t.patient_name || '';
  document.getElementById('t-service').value = t.service_name;
  document.getElementById('t-amount').value  = t.amount;
  openModal('mod-transaction');
}

async function saveTransaction() {
  const id      = document.getElementById('t-id').value;
  const pName   = document.getElementById('t-pat').value;
  const service = document.getElementById('t-service').value;
  const amount  = document.getElementById('t-amount').value;

  // Ensure aptPatients is loaded
  if (!aptPatients.length) {
    const res = await fetch('api/patients.php');
    aptPatients = (await res.json()).data;
  }

  let pat_id = null;
  if (pName) {
    const pMatch = aptPatients.find(x => x.name.toLowerCase() === pName.toLowerCase());
    if (pMatch) pat_id = pMatch.id;
  }

  const payload = { id, patient_id: pat_id, service_name: service, amount };
  await fetch('api/transactions.php', {
    method: id ? 'PUT' : 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });

  document.getElementById('t-id').value = '';
  closeModal('mod-transaction');
  showToast(id ? 'Transaction Updated!' : 'Transaction Added!');
  loadFinance();
  loadDashboard();
}

async function deleteTransaction(id) {
  showConfirm('Delete this transaction?', async () => {
    await fetch('api/transactions.php?id=' + id, { method: 'DELETE' });
    loadFinance();
    loadDashboard();
    showToast('Transaction deleted');
  });
}

// ============================================================
// MAP
// ============================================================
let mapMarkers = [];
function initMap() {
  if (mapInstance) mapInstance.remove();
  try {
    mapInstance = L.map('full-map').setView([-6.2088, 106.8456], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(mapInstance);
    loadNearbyHospitals(-6.2088, 106.8456);
  } catch (e) { console.error('Map init error:', e); }
}

async function loadNearbyHospitals(lat, lng) {
  if (!mapInstance) return;
  mapMarkers.forEach(m => mapInstance.removeLayer(m));
  mapMarkers = [];
  try {
    const query = `[out:json];node["amenity"="veterinary"](around:5000,${lat},${lng});out 10;`;
    const res   = await fetch(`https://overpass-api.de/api/interpreter?data=${encodeURIComponent(query)}`);
    const data  = await res.json();

    const centerMarker = L.marker([lat, lng]).addTo(mapInstance).bindPopup('<b>📍 Your Location</b>').openPopup();
    mapMarkers.push(centerMarker);

    if (data.elements.length === 0) {
      // Fallback: show general hospitals
      const fallback = `[out:json];node["amenity"="hospital"](around:5000,${lat},${lng});out 10;`;
      const fb = await fetch(`https://overpass-api.de/api/interpreter?data=${encodeURIComponent(fallback)}`);
      const fbData = await fb.json();
      fbData.elements.forEach(node => {
        const name   = node.tags.name || 'Hospital';
        const marker = L.marker([node.lat, node.lon]).addTo(mapInstance).bindPopup(`<b>🏥 ${name}</b><br>Healthcare Facility`);
        mapMarkers.push(marker);
      });
    } else {
      data.elements.forEach(node => {
        const name   = node.tags.name || 'Veterinary Clinic';
        const marker = L.marker([node.lat, node.lon]).addTo(mapInstance).bindPopup(`<b>🐾 ${name}</b><br>Veterinary Clinic`);
        mapMarkers.push(marker);
      });
    }
  } catch (e) { console.error('Map load error:', e); }
}

async function searchMapLocation() {
  const q = document.getElementById('map-search-input').value;
  if (!q) return;
  try {
    const res  = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}`);
    const data = await res.json();
    if (data && data.length > 0) {
      const lat = parseFloat(data[0].lat);
      const lon = parseFloat(data[0].lon);
      mapInstance.setView([lat, lon], 14);
      loadNearbyHospitals(lat, lon);
    } else {
      showToast('Location not found', 'error');
    }
  } catch (e) { showToast('Search failed', 'error'); }
}

function useMyLocation() {
  if (!navigator.geolocation) { showToast('Geolocation not supported', 'error'); return; }
  navigator.geolocation.getCurrentPosition(pos => {
    const { latitude: lat, longitude: lng } = pos.coords;
    mapInstance.setView([lat, lng], 14);
    loadNearbyHospitals(lat, lng);
    showToast('Location updated!');
  }, () => showToast('Unable to get your location', 'error'));
}

// ============================================================
// UTILS — Modal + Confirm Dialog
// ============================================================
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// FIX: Custom confirm dialog replacing browser confirm()
function showConfirm(message, onConfirm) {
  document.getElementById('confirm-msg').textContent = message;
  document.getElementById('mod-confirm').classList.add('open');
  document.getElementById('confirm-yes').onclick = () => {
    closeModal('mod-confirm');
    onConfirm();
  };
}

// ============================================================
// AI CHAT
// FIX: Uses real Anthropic API for smart veterinary responses
// ============================================================
const chatHistory = [];

async function sendChat() {
  const inp = document.getElementById('chat-input');
  const msg = inp.value.trim();
  if (!msg) return;

  const box = document.getElementById('chat-messages');
  box.innerHTML += `<div class="msg user">${msg}</div>`;
  inp.value = '';
  box.scrollTop = box.scrollHeight;

  // Show typing indicator
  const typingId = 'typing-' + Date.now();
  box.innerHTML += `<div class="msg bot" id="${typingId}">🐾 Thinking...</div>`;
  box.scrollTop = box.scrollHeight;

  chatHistory.push({ role: 'user', content: msg });

  try {
    const res = await fetch('https://api.anthropic.com/v1/messages', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        model: 'claude-sonnet-4-20250514',
        max_tokens: 1000,
        system: 'You are MedBrain, a helpful AI assistant for Vetora Luxe veterinary clinic. You help clinic staff with questions about pet health, symptoms, medications, and clinic operations. Keep responses concise and practical. Always remind users to consult a licensed veterinarian for critical decisions.',
        messages: chatHistory
      })
    });
    const data = await res.json();
    const reply = data.content?.[0]?.text || 'Sorry, I could not process that. Please try again.';

    chatHistory.push({ role: 'assistant', content: reply });

    const typingEl = document.getElementById(typingId);
    if (typingEl) typingEl.textContent = reply;
  } catch (e) {
    const typingEl = document.getElementById(typingId);
    if (typingEl) typingEl.textContent = 'Unable to connect to AI. Please check your connection.';
  }
  box.scrollTop = box.scrollHeight;
}

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('page-dashboard')) {
    setTimeout(() => initPageData('dashboard'), 100);
  }
});
