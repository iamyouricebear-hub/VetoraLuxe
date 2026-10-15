<?php
require_once 'db.php';
$id = $_GET['id'];

// Ambil data transaksi beserta detail lengkap pasien
$sql = "SELECT t.*, p.name, p.blood_type AS breed, p.phone, p.addr, p.gender, p.dob, p.allergy 
        FROM transactions t 
        JOIN patients p ON t.patient_id = p.id 
        WHERE t.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) die("Invoice not found");

// Menghitung umur
$age = "Unknown";
if (!empty($data['dob'])) {
    $dob = new DateTime($data['dob']);
    $now = new DateTime();
    $age = $now->diff($dob)->y . " Years";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #INV-<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?></title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@400;500;600;700&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #FFF8F6;
            --text-main: #3A2E39;
            --text-muted: #7E6272;
            --primary: #E8A0BF;
            --secondary: #BFA2DB;
            --accent: #F6D6E0;
            --card-bg: rgba(255, 255, 255, 0.85);
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: var(--bg); 
            color: var(--text-main); 
            padding: 40px; 
            display: flex;
            justify-content: center;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .invoice-wrapper {
            background: #ffffff;
            width: 100%;
            max-width: 800px;
            border-radius: 36px;
            box-shadow: 0 20px 40px -10px rgba(191, 162, 171, 0.15), inset 0 2px 5px white;
            border: 2px solid #FDF0F4;
            padding: 50px;
            position: relative;
            overflow: hidden;
        }

        /* Decorative blobs for invoice */
        .invoice-wrapper::before {
            content: ''; position: absolute; top: -100px; left: -100px; width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(232, 160, 191, 0.15) 0%, transparent 70%); border-radius: 50%;
        }
        .invoice-wrapper::after {
            content: ''; position: absolute; bottom: -100px; right: -100px; width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(191, 162, 219, 0.12) 0%, transparent 70%); border-radius: 50%;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px dashed var(--accent);
            padding-bottom: 30px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .clinic-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .logo-mark {
            width: 64px; height: 64px; background: linear-gradient(145deg, #FADCE5 0%, #F5C6D8 100%);
            border-radius: 24px; display: flex; align-items: center; justify-content: center; font-size: 28px;
            box-shadow: inset 0 2px 4px white, 0 6px 12px -4px rgba(232, 160, 191, 0.25); border: 2px solid white;
        }

        .clinic-info h1 {
            font-family: 'Sora', sans-serif; font-size: 1.8rem; color: #3f2e39;
            letter-spacing: -0.02em; margin-bottom: 4px;
        }
        .clinic-info p {
            font-size: 0.9rem; color: var(--text-muted); font-weight: 500; display:flex; align-items:center; gap:6px;
        }

        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 2.2rem;
            color: var(--secondary);
            margin-bottom: 4px;
            letter-spacing: -0.02em;
        }
        .invoice-title p {
            font-weight: 600; color: var(--text-muted); font-size: 1rem;
        }
        .status-chip {
            display: inline-block; margin-top: 10px; padding: 6px 16px; border-radius: 20px;
            background: #E8F5E9; color: #2E7D32; font-weight: 600; font-size: 0.8rem; border: 1px solid #C8E6C9;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .info-card {
            background: rgba(255,255,255,0.6);
            border: 1.5px solid var(--accent);
            padding: 20px 24px;
            border-radius: 24px;
        }
        .info-card h3 {
            font-family: 'Poppins', sans-serif; font-size: 1.1rem; color: #4a3641; margin-bottom: 12px;
            display: flex; align-items: center; gap: 8px;
        }
        .info-list {
            list-style: none;
        }
        .info-list li {
            margin-bottom: 8px; font-size: 0.95rem; display: flex; align-items: flex-start;
        }
        .info-list li span.lbl {
            min-width: 100px; color: var(--text-muted); font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding-top: 3px;
        }
        .info-list li span.val {
            font-weight: 600; color: var(--text-main); flex: 1;
        }

        .table-wrap {
            background: rgba(255,255,255,0.5); border: 1.5px solid var(--accent);
            border-radius: 24px; overflow: hidden; margin-bottom: 30px; position: relative; z-index: 1;
        }
        table {
            width: 100%; border-collapse: collapse; text-align: left;
        }
        thead th {
            background: linear-gradient(120deg, #F6D6E0, #FCE9EF); padding: 16px 24px;
            color: #7E6272; font-family: 'Sora', sans-serif; font-size: 0.85rem; font-weight: 700; text-transform:uppercase; letter-spacing:0.04em;
        }
        tbody td {
            padding: 20px 24px; border-bottom: 1px dashed var(--accent); color: #4e3643; font-weight: 500;
        }
        tbody tr:last-child td { border-bottom: none; }

        .summary-box {
            display: flex; justify-content: flex-end; position: relative; z-index: 1;
        }
        .summary-content {
            width: 320px;
            background: linear-gradient(145deg, #FAF0F4, #FFFFFF); border: 1.5px solid var(--accent);
            border-radius: 24px; padding: 24px;
        }
        .summary-row {
            display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.95rem; color: var(--text-muted); font-weight: 600;
        }
        .summary-row.total {
            margin-top: 16px; padding-top: 16px; border-top: 2px dashed var(--accent);
            color: var(--secondary); font-size: 1.5rem; font-weight: 700; font-family: 'Poppins', sans-serif;
        }

        .footer {
            margin-top: 50px; text-align: center; color: var(--text-muted); position: relative; z-index: 1; border-top: 2px dashed var(--accent); padding-top: 30px;
        }
        .footer p { font-size: 0.95rem; font-weight: 500; margin-bottom: 6px; }
        .footer .heart { color: var(--primary); font-size: 1.2rem; }
        .footer .signature-cute {
            font-family: 'Sora', sans-serif; font-weight: 700; color: var(--primary); font-size: 1.1rem;
            margin-top: 16px; opacity: 0.8; letter-spacing: 0.05em; text-transform: uppercase;
        }

        @media print {
            body { padding: 0; background: white; }
            .invoice-wrapper { border: none; box-shadow: none; max-width: 100%; border-radius: 0; padding: 20px; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="invoice-wrapper">
    
    <div class="header">
        <div class="clinic-brand">
            <div class="logo-mark">🌸</div>
            <div class="clinic-info">
                <h1>Vetora Luxe</h1>
                <p>Premium Veterinary Care 🤍</p>
                <p style="margin-top:2px;">Jakarta, Indonesia</p>
            </div>
        </div>
        <div class="invoice-title">
            <h2>INVOICE</h2>
            <p>#INV-<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?></p>
            <p>Date: <?php echo date("d M Y", strtotime($data['transaction_date'])); ?></p>
            <div class="status-chip">Paid / Settled</div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-card">
            <h3>🐾 Patient Information</h3>
            <ul class="info-list">
                <li><span class="lbl">Name</span> <span class="val"><?php echo htmlspecialchars($data['name']); ?></span></li>
                <li><span class="lbl">Species</span> <span class="val"><?php echo htmlspecialchars($data['breed']); ?></span></li>
                <li><span class="lbl">Gender</span> <span class="val"><?php echo htmlspecialchars($data['gender']); ?></span></li>
                <li><span class="lbl">Age</span> <span class="val"><?php echo $age; ?></span></li>
                <li><span class="lbl">Allergies</span> <span class="val" style="color:#D4A373;"><?php echo htmlspecialchars($data['allergy']); ?></span></li>
            </ul>
        </div>
        <div class="info-card">
            <h3>👤 Owner Details</h3>
            <ul class="info-list">
                <li><span class="lbl">Contact</span> <span class="val"><?php echo htmlspecialchars($data['phone'] ?: '-'); ?></span></li>
                <li><span class="lbl">Address</span> <span class="val"><?php echo htmlspecialchars($data['addr'] ?: '-'); ?></span></li>
            </ul>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Description of Service</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="font-weight: 600; font-size: 1.05rem;"><?php echo htmlspecialchars($data['service_name']); ?></div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 6px;">Veterinary medical service & consultation</div>
                    </td>
                    <td style="text-align: right; font-weight: 600; font-family: 'Poppins', sans-serif;">
                        Rp <?php echo number_format($data['amount'], 0, ',', '.'); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="summary-box">
        <div class="summary-content">
            <div class="summary-row">
                <span>Subtotal</span>
                <span>Rp <?php echo number_format($data['amount'], 0, ',', '.'); ?></span>
            </div>
            <div class="summary-row">
                <span>Tax (0%)</span>
                <span>Rp 0</span>
            </div>
            <div class="summary-row total">
                <span>Total</span>
                <span>Rp <?php echo number_format($data['amount'], 0, ',', '.'); ?></span>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Thank you for entrusting us with your beloved pet! <span class="heart">♥</span></p>
        <p>Their health and happiness are our greatest priority.</p>
        <div class="signature-cute">Vetora Luxe Clinic</div>
    </div>

</div>

</body>
</html>