<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Indoor Patient Register';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>UJJIVAN HOSPITAL - <?php echo e($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #16a085;
            --accent: #e74c3c;
            --success: #27ae60;
            --warning: #f39c12;
            --soft: #f4f8fb;
            --border: #d9e5ec;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #e8f8f5 0%, #f8fdfb 48%, #dfefea 100%);
            color: var(--primary);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .app-navbar {
            position: sticky;
            top: 0;
            z-index: 1020;
            background: linear-gradient(90deg, var(--primary), #1a6d5a);
            box-shadow: 0 10px 28px rgba(44, 62, 80, 0.25);
        }

        .navbar-brand, .nav-link { color: #fff !important; }
        .navbar-brand { font-weight: 800; letter-spacing: 0.5px; }

        .page-shell {
            width: min(96vw, 2200px);
            margin: 24px auto;
        }

        .hospital-card,
        .edit-card,
        .stats-card,
        .records-modal .modal-content {
            border: 1px solid rgba(22, 160, 133, 0.16);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(44, 62, 80, 0.12);
        }

        .hospital-header {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 18px;
            align-items: center;
            padding: 22px;
            border-radius: 12px 12px 0 0;
            background: linear-gradient(120deg, #ffffff, #eef8f5);
            border-bottom: 4px solid var(--secondary);
        }

        .ntpc-logo {
            width: 110px;
            height: 76px;
            display: grid;
            place-items: center;
            border: 3px solid var(--primary);
            border-radius: 12px;
            color: var(--primary);
            font-weight: 900;
            line-height: 1.1;
            text-align: center;
            background: #fff;
        }

        .hospital-title { text-align: center; }
        .hospital-title h1 {
            margin: 0;
            color: var(--primary);
            font-size: clamp(1.7rem, 3vw, 3rem);
            font-weight: 900;
        }

        .hospital-title h2 {
            margin: 4px 0 0;
            color: var(--secondary);
            font-size: clamp(1rem, 1.6vw, 1.45rem);
            font-weight: 800;
        }

        .clock-box {
            min-width: 210px;
            padding: 12px 18px;
            border-radius: 999px;
            color: #fff;
            text-align: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        .clock-box .time {
            display: block;
            font-family: Consolas, monospace;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .toolbar,
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
        }

        .btn-med {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 999px;
            padding: 11px 20px;
            color: #fff;
            font-weight: 800;
            text-decoration: none;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .btn-med:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(44, 62, 80, .18);
        }

        .btn-primary-med { background: linear-gradient(45deg, var(--primary), #1a6d5a); }
        .btn-danger-med { background: linear-gradient(45deg, #c0392b, var(--accent)); }
        .btn-success-med { background: linear-gradient(45deg, #1e8449, var(--success)); }
        .btn-warning-med { background: linear-gradient(45deg, var(--warning), #f7c948); color: #2c3e50; }
        .btn-muted-med { background: #6c757d; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(160px, 1fr));
            gap: 14px;
            margin: 18px 0;
        }

        .stats-card {
            padding: 16px;
            border-left: 5px solid var(--secondary);
        }

        .stats-card .label { color: #6c7a86; font-size: .85rem; font-weight: 700; }
        .stats-card .value { color: var(--primary); font-size: 1.7rem; font-weight: 900; }

        .table-zone {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .register-table {
            min-width: 1900px;
            width: 100%;
            margin: 0;
            border-collapse: collapse;
        }

        .register-table th {
            position: sticky;
            top: 0;
            z-index: 2;
            padding: 12px 8px;
            color: #fff;
            background: linear-gradient(90deg, var(--primary), #1a6d5a);
            border: 1px solid rgba(255,255,255,.15);
            font-size: .77rem;
            text-align: center;
            text-transform: uppercase;
        }

        .register-table td {
            border: 1px solid var(--border);
            background: #fff;
            vertical-align: top;
        }

        .register-table tbody tr:nth-child(even) td { background: #f7fbf9; }
        .register-table tbody tr:hover td { background: #fef8f0; }

        .register-table input,
        .register-table select,
        .register-table textarea,
        .edit-card input,
        .edit-card select,
        .edit-card textarea {
            width: 100%;
            min-height: 38px;
            border: 1px solid transparent;
            padding: 8px;
            background: transparent;
            color: var(--primary);
            font-size: .86rem;
        }

        .register-table textarea { min-height: 58px; resize: vertical; }
        .register-table input:focus,
        .register-table select:focus,
        .register-table textarea:focus {
            outline: 0;
            border-color: var(--secondary);
            border-radius: 8px;
            background: #eef8f5;
        }

        .action-btn {
            border: 0;
            border-radius: 999px;
            padding: 7px 10px;
            color: #fff;
            background: var(--accent);
            font-weight: 800;
        }

        .records-modal .modal-dialog { max-width: min(1500px, 96vw); }
        .modal-content { overflow: hidden; }
        .modal-header {
            color: #fff;
            background: linear-gradient(90deg, var(--primary), #1a6d5a);
        }

        .filter-bar {
            padding: 14px;
            margin-bottom: 14px;
            border-radius: 12px;
            background: var(--soft);
        }

        .toast-container { z-index: 2000; }

        #loadingOverlay {
            position: fixed;
            inset: 0;
            z-index: 3000;
            display: none;
            place-items: center;
            background: rgba(255,255,255,.72);
            backdrop-filter: blur(3px);
        }

        .spinner-panel {
            display: flex;
            gap: 12px;
            align-items: center;
            border-radius: 999px;
            padding: 16px 24px;
            color: #fff;
            background: linear-gradient(90deg, var(--primary), #1a6d5a);
            box-shadow: 0 20px 40px rgba(44,62,80,.25);
            font-weight: 800;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 6px 10px;
        }

        .print-header, .print-info { margin-bottom: 16px; }
        .print-table { width: 100%; border-collapse: collapse; background: #fff; }
        .print-table th, .print-table td {
            border: 1px solid #444;
            padding: 6px;
            font-size: 10px;
            vertical-align: top;
        }
        .print-table th { color: #fff; background: var(--primary); }

        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-thumb { background: #9fd4c7; border-radius: 999px; }
        ::-webkit-scrollbar-track { background: #eef5f2; }

        @page { size: A4 landscape; margin: 10mm; }

        @media print {
            .app-navbar, .no-print, .toolbar, .modal, #loadingOverlay, .toast-container { display: none !important; }
            body { background: #fff; }
            .page-shell, .hospital-card { width: 100%; margin: 0; box-shadow: none; border: 0; }
            .hospital-header { border-bottom: 2px solid #000; }
            .print-table th, .register-table th {
                color: #000 !important;
                background: #e8e8e8 !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }

        @media (max-width: 900px) {
            .hospital-header { grid-template-columns: 1fr; text-align: center; }
            .ntpc-logo, .clock-box { margin: 0 auto; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .btn-med, .filter-bar input, .filter-bar button { width: 100%; }
        }

        @media (max-width: 560px) {
            .page-shell { width: 100%; margin: 0; }
            .hospital-card { border-radius: 0; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg app-navbar no-print">
    <div class="container-fluid">
        <a class="navbar-brand" href="indoor_index.php"><i class="fa-solid fa-hospital-user"></i> Indoor Register Management System</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="indoor_index.php">Dashboard</a>
            <a class="nav-link" href="indoor_print_records.php" target="_blank">Print</a>
        </div>
    </div>
</nav>