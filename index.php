<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$user_name = $_SESSION['name'] ?? 'Admin';
$user_role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>🌸 Vetora Luxe · Premium Veterinary Care</title>
    <!-- Fonts: Poppins + Sora + Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&family=Poppins:wght@300;400;500;600;700&family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
<div id="toast-container" class="toast-container"></div>
<div class="decor-cat"></div>
<div class="yarn-ball">🧶</div>
<div class="bg-blob-1"></div>
<div class="bg-blob-2"></div>

<?php if (!$isLoggedIn): ?>
<!-- ===================== LOGIN PAGE ===================== -->
<div id="page-login">
  <div class="login-box">
    <div class="login-left">
      <h1>🌸 Vetora Luxe</h1>
      <p>Premium care for the ones who matter most.</p>
      <form id="login-form" onsubmit="event.preventDefault(); doLogin();">
        <div class="input-grp" style="margin-bottom:16px;">
          <input type="email" id="login-email" value="admin@kiyowo.id" required>
        </div>
        <div class="input-grp" style="margin-bottom:24px;">
          <input type="password" id="login-pass" value="admin123" required>
        </div>
        <button type="submit" class="btn" style="width:100%; padding:14px; font-size:16px;">Login</button>
      </form>
    </div>
    <div class="login-right"></div>
  </div>
</div>
<script src="js/app.js?v=<?= time() ?>"></script>
<script>feather.replace();</script>
</body>
</html>
<?php exit; endif; ?>

<!-- ===================== MAIN APP ===================== -->
<div class="app">
  <!-- Minimal Icon Nav -->
  <div class="icon-nav">
    <div class="nav-icons">
      <!-- Grid = Dashboard -->
      <div class="nav-item active" onclick="nav('dashboard', this)" title="Dashboard"><i data-feather="grid"></i></div>

      <div class="nav-item" onclick="nav('finance', this)" title="Finance">
      <i data-feather="dollar-sign"></i>
      </div>

      <!-- Users = Pets -->
      <div class="nav-item has-notif" onclick="nav('patients', this)" title="Pets"><i data-feather="users"></i></div>
      <!-- Activity = Vets -->
      <div class="nav-item" onclick="nav('doctors', this)" title="Vets"><i data-feather="activity"></i></div>
      <!-- Calendar = Appointments -->
      <div class="nav-item" onclick="nav('appointments', this)" title="Appointments"><i data-feather="calendar"></i></div>
      <!-- Database = Records -->
      <div class="nav-item" onclick="nav('records', this)" title="Records"><i data-feather="file-text"></i></div>
      <!-- Package = Pharmacy -->
      <div class="nav-item" onclick="nav('pharmacy', this)" title="Pharmacy"><i data-feather="package"></i></div>
      <!-- Map = Maps -->
      <div class="nav-item" onclick="nav('maps', this)" title="Map & Locator"><i data-feather="map-pin"></i></div>
      <!-- Smile = AI Helper -->
      <div class="nav-item" onclick="nav('ai-chat', this)" title="AI Helper"><i data-feather="smile"></i></div>
      <!-- Log out -->
      <div class="nav-item" onclick="doLogout()" style="margin-top:auto;" title="Sign Out"><i data-feather="log-out" style="color:#F28482;"></i></div>
    </div>
  </div>

  <div class="main-panel">
    <!-- ======= DASHBOARD ======= -->
    <div class="page-content active" id="page-dashboard">
      <div class="central-area">
        <div class="floating-topbar">
          <div class="profile-area">
            <div class="notif-icon"><i data-feather="bell" style="width:20px; height:20px;"></i></div>
            <div class="admin-avatar">A</div>
          </div>
        </div>
        <div class="hero-greeting">
          <h1><span id="dash-greeting">Good Morning, </span><span><?= htmlspecialchars($user_name) ?></span> 👋</h1>
          <div class="hero-sub">✨ All animals are under care today — you're doing amazing 🌷</div>
        </div>
        
        <!-- Stats -->
        <div class="stats-row" id="dash-stats">
          <!-- Populated by JS -->
        </div>

        <!-- Today's Queue (replaces old table, now outputs flow rows via JS) -->
        <div class="flow-section">
          <div class="flow-header">
            <h3>🐕🦺 Patient flow · today <span style="font-size:0.9rem; font-weight:400; margin-left:8px; color:#BFA2DB;">real‑time</span></h3>
            <button class="btn btn-sm" onclick="openModal('mod-apt')">+ Schedule Checkup</button>
          </div>
          <div id="dash-queue-list">
            <!-- Populated by JS as flow rows -->
          </div>
        </div>
      </div>

      <!-- Right Panel -->
      <div class="right-panel">
        <div class="panel-card" style="flex:1;">
          <div class="panel-title">📈 🌸 Live activity</div>
          <div id="recent-activity-feed">
            <!-- Populated by JS from real data -->
          </div>
          
          <div class="panel-title" style="margin-top:40px;">👥 Vet availability</div>
          <div id="vet-availability-list">
            <!-- Populated by JS from real data -->
          </div>
          <div class="cute-footer">🤍 ensuring 24/7 care</div>
        </div>
      </div>
    </div>

    <!-- ======= PETS ======= -->
    <div class="page-content" id="page-patients">
      <div class="flow-section" style="width:100%;">
        <div class="flow-header">
          <h3>🐾 Pet Profiles</h3>
          <button class="btn" onclick="openModal('mod-patient')">+ Register Pet</button>
        </div>
        <div class="search-wrapper mb-4">
          <i data-feather="search"></i>
          <input type="text" id="srch-pat" placeholder="Search pets..." onkeyup="loadPatients()">
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Pet & Info</th><th>Contact</th><th>Blood Type & Allergy</th><th>Address</th><th>Actions</th></tr></thead>
            <tbody id="patients-list"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ======= VETS ======= -->
    <div class="page-content" id="page-doctors">
      <div class="flow-section" style="width:100%;">
        <div class="flow-header">
          <h3>👩‍⚕️ Animal Doctors</h3>
          <button class="btn" onclick="openModal('mod-doctor')">+ Add Doctor</button>
        </div>
        <div class="search-wrapper mb-4">
          <i data-feather="search"></i>
          <input type="text" id="srch-doc" placeholder="Search doctors..." onkeyup="loadDoctors()">
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Animal Doctor</th><th>Specialty</th><th>Schedule</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody id="doctors-list"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ======= APPOINTMENTS ======= -->
    <div class="page-content" id="page-appointments">
      <div class="flow-section" style="width:100%;">
        <div class="flow-header">
          <h3>📅 Appointment Schedule</h3>
          <button class="btn" onclick="openModal('mod-apt')">+ Schedule</button>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Schedule</th><th>Pet</th><th>Veterinarian</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody id="apts-list"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ======= RECORDS ======= -->
    <div class="page-content" id="page-records">
        <div class="flow-section" style="width:350px;">
          <div class="flow-header" style="margin-bottom:12px;"><h3>Pet Selection</h3></div>
          <div id="record-pat-list" style="display:flex; flex-direction:column; gap:8px;"></div>
        </div>
        <div class="flow-section" style="flex:1;">
          <div class="flow-header">
            <h3 id="rec-pat-title">Medical History</h3>
            <button class="btn btn-sm" onclick="openRecordModal()" id="btn-add-rec" style="display:none;">+ Add Record</button>
          </div>
          <div id="record-detail-list">
             <div style="color:#BFA2DB; text-align:center; padding:60px 40px;">Select a pet from the list to view history 🐾</div>
          </div>
        </div>
    </div>

    <!-- ======= PHARMACY ======= -->
    <div class="page-content" id="page-pharmacy">
      <div class="flow-section" style="width:100%;">
        <div class="flow-header">
          <h3>💊 Pharmacy Inventory</h3>
          <button class="btn" onclick="openModal('mod-drug')">+ Add Stock</button>
        </div>
        <div class="search-wrapper mb-4">
          <i data-feather="search"></i>
          <input type="text" id="srch-drug" placeholder="Search medicine..." onkeyup="loadDrugs()">
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Medicine Name</th><th>Stock Level</th><th>Unit Price ($)</th><th>Actions</th></tr></thead>
            <tbody id="drugs-list"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ======= MAPS ======= -->
    <div class="page-content" id="page-maps">
      <div class="flow-section" style="width:100%; display:flex; flex-direction:column;">
        <div class="flow-header"><h3>🗺️ Clinic Map & Locator</h3></div>
        <div class="flex-between mb-4">
          <div class="search-wrapper">
            <i data-feather="search"></i>
            <input type="text" id="map-search-input" placeholder="Search address to find nearest clinic...">
          </div>
          <div style="display:flex; gap:12px;">
            <button class="btn" onclick="searchMapLocation()">Search Maps</button>
            <button class="btn btn-secondary" onclick="useMyLocation()">📍 My Location</button>
          </div>
        </div>
        <div id="map-wrap" style="flex:1;">
          <div id="full-map" style="height:100%; width:100%;"></div>
        </div>
      </div>
    </div>

    <!-- ======= FINANCE ======= -->
<div class="page-content" id="page-finance">
  <div class="flow-section" style="width:100%;">
    
    <div class="flow-header">
      <h3>💰 Financial Report</h3>
      <button class="btn btn-sm" onclick="openModal('mod-transaction')" style="margin-left: auto;">+ Add Transaction</button>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; gap:16px;">
      <div class="search-wrapper">
        <i data-feather="search"></i>
        <input type="text" id="srch-trans" placeholder="Search transactions..." onkeyup="loadFinance()">
      </div>
      <div style="font-weight:600; font-size:1.1rem; color:#4e3643; white-space:nowrap;">
        Total Revenue: <span id="finance-total" style="color:#D4A373;">Rp 0</span>
      </div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Patient</th>
            <th>Service</th>
            <th>Amount</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="trans-list"></tbody>
      </table>
    </div>

  </div>
</div>

    <!-- ======= AI CHAT ======= -->
    <div class="page-content" id="page-ai-chat">
      <div class="flow-section" style="width:100%;">
        <div class="flow-header"><h3>🤖 MedBrain AI Helper</h3></div>
        <div class="chat-container">
          <div class="chat-messages" id="chat-messages">
            <div class="msg bot">Hello! I am your AI Veterinary Assistant. I can help analyze symptoms, list drug stock, or manage pet schedules. How can I help?</div>
          </div>
          <div class="chat-input-area">
            <input type="text" id="chat-input" placeholder="Type a message..." onkeypress="if(event.key==='Enter') sendChat()">
            <button class="btn" onclick="sendChat()">Send</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===================== MODALS ===================== -->
<div class="modal-overlay" id="mod-patient" onclick="if(event.target==this)closeModal('mod-patient')">
  <div class="modal">
    <div class="modal-header"><h3>Manage Pet Profile</h3><button class="btn-close" onclick="closeModal('mod-patient')"><i data-feather="x"></i></button></div>
    <form onsubmit="event.preventDefault(); savePatient();">
      <div class="modal-body">
        <input type="hidden" id="p-id">
        <div class="input-grp"><label style="font-weight:600;margin-bottom:6px;display:block;">Pet & Owner Names</label><input type="text" id="p-name" placeholder="e.g. 🐶 Bruno | Owner: Sarah" required></div>
        <div class="input-grp"><label style="font-weight:600;margin-bottom:6px;display:block;">Owner Phone</label><input type="text" id="p-phone"></div>
        <div class="input-grp"><label style="font-weight:600;margin-bottom:6px;display:block;">Address</label><input type="text" id="p-addr"></div>
        <div style="display:flex; gap:16px;">
          <div class="input-grp" style="flex:1;"><label style="font-weight:600;margin-bottom:6px;display:block;">Gender</label><select id="p-gender"><option>Male</option><option>Female</option></select></div>
          <div class="input-grp" style="flex:1.5;"><label style="font-weight:600;margin-bottom:6px;display:block;">Species / Breed</label><input type="text" id="p-blood" placeholder="e.g. Dog (Golden)" required></div>
          <div class="input-grp" style="flex:1;"><label style="font-weight:600;margin-bottom:6px;display:block;">Date of Birth</label><input type="date" id="p-dob"></div>
        </div>
        <div class="input-grp"><label style="font-weight:600;margin-bottom:6px;display:block;">Allergies</label><input type="text" id="p-allergy" value="None"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" style="background:transparent; color:#7E6272;" onclick="closeModal('mod-patient')">Cancel</button>
        <button type="submit" class="btn">Save Pet</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="mod-doctor" onclick="if(event.target==this)closeModal('mod-doctor')">
  <div class="modal">
    <div class="modal-header"><h3>Manage Animal Doctor</h3><button class="btn-close" onclick="closeModal('mod-doctor')"><i data-feather="x"></i></button></div>
    <form onsubmit="event.preventDefault(); saveDoctor();">
      <div class="modal-body">
        <input type="hidden" id="d-id">
        <div class="input-grp" style="margin-bottom:12px;"><label style="font-weight:600;margin-bottom:6px;display:block;">Doctor Name</label><input type="text" id="d-name" required></div>
        <div class="input-grp" style="margin-bottom:12px;"><label style="font-weight:600;margin-bottom:6px;display:block;">Specialty</label><input type="text" id="d-spec" required></div>
        <div class="input-grp"><label style="font-weight:600;margin-bottom:6px;display:block;">Practice Hours</label><input type="text" id="d-hours" value="Mon-Fri 09:00-17:00"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('mod-doctor')" style="background:transparent; color:#7E6272;">Cancel</button>
        <button type="submit" class="btn">Save Doctor</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="mod-apt" onclick="if(event.target==this)closeModal('mod-apt')">
  <div class="modal">
    <div class="modal-header"><h3>Schedule Checkup</h3><button class="btn-close" onclick="closeModal('mod-apt')"><i data-feather="x"></i></button></div>
    <form onsubmit="event.preventDefault(); saveApt();">
      <div class="modal-body">
        <input type="hidden" id="a-id">
        <div class="input-grp" style="margin-bottom:12px;"><label style="font-weight:600;margin-bottom:6px;display:block;">Pet / Owner Name</label><input list="pat-list" id="a-pat" placeholder="Search or type name..." autocomplete="off" required><datalist id="pat-list"></datalist></div>
        <div class="input-grp" style="margin-bottom:12px;"><label style="font-weight:600;margin-bottom:6px;display:block;">Vet</label><input list="doc-list" id="a-doc" placeholder="Search or type name..." autocomplete="off" required><datalist id="doc-list"></datalist></div>
        <div style="display:flex; gap:16px; margin-bottom:12px;">
          <div class="input-grp" style="flex:1;"><label style="font-weight:600;margin-bottom:6px;display:block;">Date</label><input type="date" id="a-date" required></div>
          <div class="input-grp" style="flex:1;"><label style="font-weight:600;margin-bottom:6px;display:block;">Time</label><input type="time" id="a-time" required></div>
        </div>
        <div class="input-grp"><label style="font-weight:600;margin-bottom:6px;display:block;">Status</label>
          <select id="a-status"><option>Scheduled</option><option>Needs Attention</option><option>Recovering</option><option>Urgent Care</option></select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('mod-apt')" style="background:transparent; color:#7E6272;">Cancel</button>
        <button type="submit" class="btn">Save Appointment</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="mod-drug" onclick="if(event.target==this)closeModal('mod-drug')">
  <div class="modal">
    <div class="modal-header"><h3>Add / Edit Drug Stock</h3><button class="btn-close" onclick="closeModal('mod-drug')"><i data-feather="x"></i></button></div>
    <form onsubmit="event.preventDefault(); saveDrug();">
      <div class="modal-body">
        <input type="hidden" id="drug-id">
        <div class="input-grp" style="margin-bottom:12px;">
          <label style="font-weight:600;margin-bottom:6px;display:block;color:#7E6272;font-size:13px;letter-spacing:0.5px;">💊 Medicine Name</label>
          <input type="text" id="drug-name-input" placeholder="e.g. Amoxiclav 250mg" required>
        </div>
        <div style="display:flex; gap:16px;">
          <div class="input-grp" style="flex:1;"><label style="font-weight:600;margin-bottom:6px;display:block;">Stock (units)</label><input type="number" id="drug-stock" min="0" required></div>
          <div class="input-grp" style="flex:1;"><label style="font-weight:600;margin-bottom:6px;display:block;">Price (Rp)</label><input type="number" id="drug-price" step="0.01" min="0" required></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('mod-drug')" style="background:transparent; color:#7E6272;">Cancel</button>
        <button type="submit" class="btn">Save Stock</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="mod-transaction" onclick="if(event.target==this)closeModal('mod-transaction')">
  <div class="modal">
    <div class="modal-header"><h3>Manage Transaction</h3><button class="btn-close" onclick="closeModal('mod-transaction')"><i data-feather="x"></i></button></div>
    <form onsubmit="event.preventDefault(); saveTransaction();">
      <div class="modal-body">
        <input type="hidden" id="t-id">
        <div class="input-grp" style="margin-bottom:12px;"><label style="font-weight:600;margin-bottom:6px;display:block;">Patient Name (Optional)</label><input list="pat-list" id="t-pat" placeholder="Search or type name..." autocomplete="off"></div>
        <div class="input-grp" style="margin-bottom:12px;"><label style="font-weight:600;margin-bottom:6px;display:block;">Service Name</label><input type="text" id="t-service" required></div>
        <div class="input-grp"><label style="font-weight:600;margin-bottom:6px;display:block;">Amount ($/Rp)</label><input type="number" id="t-amount" min="0" required></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('mod-transaction')" style="background:transparent; color:#7E6272;">Cancel</button>
        <button type="submit" class="btn">Save Transaction</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== MODAL: MEDICAL RECORD (replaces old prompt()) ===== -->
<div class="modal-overlay" id="mod-record" onclick="if(event.target==this)closeModal('mod-record')">
  <div class="modal">
    <div class="modal-header">
      <h3>📋 Add Medical Record</h3>
      <button class="btn-close" onclick="closeModal('mod-record')"><i data-feather="x"></i></button>
    </div>
    <form onsubmit="event.preventDefault(); saveRecord();">
      <div class="modal-body">
        <div style="font-weight:600; color:#BFA2DB; margin-bottom:16px; font-size:0.9rem;" id="rec-pat-label"></div>
        <div class="input-grp" style="margin-bottom:12px;">
          <label style="font-weight:600;margin-bottom:6px;display:block;">Doctor</label>
          <select id="rec-doctor-id" required>
            <option value="">Select Doctor...</option>
          </select>
        </div>
        <div class="input-grp" style="margin-bottom:12px;">
          <label style="font-weight:600;margin-bottom:6px;display:block;">Diagnosis</label>
          <input type="text" id="rec-diagnosis" placeholder="e.g. Skin allergy, ear infection..." required>
        </div>
        <div class="input-grp">
          <label style="font-weight:600;margin-bottom:6px;display:block;">Therapy / Treatment</label>
          <input type="text" id="rec-therapy" placeholder="e.g. Antihistamine, antibiotic drops...">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('mod-record')" style="background:transparent;color:#7E6272;">Cancel</button>
        <button type="submit" class="btn">Save Record</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== MODAL: CONFIRM DIALOG (replaces browser confirm()) ===== -->
<div class="modal-overlay" id="mod-confirm">
  <div class="modal" style="max-width:380px;">
    <div class="modal-header"><h3>⚠️ Confirm Action</h3></div>
    <div class="modal-body">
      <p id="confirm-msg" style="font-size:1rem; color:#4e3643; line-height:1.6;"></p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('mod-confirm')" style="background:transparent;color:#7E6272;">Cancel</button>
      <button class="btn btn-danger" id="confirm-yes">Yes, Delete</button>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="js/app.js?v=<?= time() ?>"></script>
<script>feather.replace();</script>
</body>
</html>
