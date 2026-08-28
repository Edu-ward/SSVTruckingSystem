<?php
require_once __DIR__ . '/../includes/security_headers.php';
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized Access");
}

if (!isset($_GET['id'])) {
    die("Ticket ID not specified.");
}

$ticket_id = intval($_GET['id']);

$stmt = $pdo->prepare("
    SELECT d.*, 
           t.truck_code, 
           CONCAT(tr.first_name, ' ', tr.last_name) AS driver_name, 
           tr.phone AS driver_phone
    FROM dispatches d
    LEFT JOIN trucks t ON d.truck_id = t.id
    LEFT JOIN drivers tr ON d.driver_id = tr.id
    WHERE d.id = ?
");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die("Ticket not found.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Waybill - <?= htmlspecialchars($ticket['ticket_number']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        @page {
            size: A5 portrait;
            margin: 0;
        }

        @media print {

            html,
            body {
                width: 148mm !important;
                height: 210mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                overflow: hidden !important;
            }

            .no-print {
                display: none !important;
            }

            .page-wrapper {
                width: 148mm !important;
                height: 210mm !important;
                padding: 3mm 4mm !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
            }

            .ticket-copy {
                flex: 1 !important;
                overflow: hidden !important;
            }

            .cut-line {
                border: none !important;
                border-top: 1px dashed #aaa !important;
                margin: 0 !important;
                page-break-after: avoid !important;
            }
        }

        body {
            background: #e5e7eb;
            font-family: 'Inter', sans-serif;
        }

        /* Preview wrapper */
        .preview-shell {
            display: flex;
            justify-content: center;
            padding-top: 80px;
            padding-bottom: 30px;
        }

        /* Simulates A5 paper */
        .page-wrapper {
            background: #fff;
            width: 148mm;
            height: 210mm;
            padding: 3mm 4mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        /* Cut line between copies */
        .cut-line {
            border: none;
            border-top: 1px dashed #aaa;
            margin: 0;
            position: relative;
        }

        .cut-line::before {
            content: '\2702';
            position: absolute;
            left: -14px;
            top: -9px;
            font-size: 11px;
            color: #aaa;
        }

        /* Each ticket copy */
        .ticket-copy {
            flex: 1;
            padding: 2mm 2mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        /* Header */
        .tc-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1.5px solid #111;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }

        .tc-header .brand {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .tc-header .brand img {
            height: 26px;
            width: auto;
        }

        .tc-header .brand-text h1 {
            font-size: 11px;
            font-weight: 700;
            line-height: 1.2;
        }

        .tc-header .brand-text p {
            font-size: 7px;
            color: #666;
            line-height: 1.3;
        }

        .tc-header .ticket-id {
            text-align: right;
        }

        .tc-header .ticket-id .label {
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #555;
        }

        .tc-header .ticket-id .number {
            font-size: 9px;
            font-weight: 700;
            color: #1d4ed8;
            font-family: monospace;
        }

        /* Body grid */
        .tc-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3px;
            margin-bottom: 4px;
        }

        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 3px 5px;
        }

        .info-box .box-label {
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #888;
            margin-bottom: 2px;
        }

        .info-box .driver-name {
            font-size: 9px;
            font-weight: 700;
            color: #111;
            line-height: 1.3;
        }

        .info-box .driver-phone {
            font-size: 8px;
            color: #555;
        }

        .info-box .vehicle-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 3px;
            padding-top: 3px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
        }

        .info-box .vehicle-badge {
            font-family: monospace;
            font-weight: 700;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 1px 5px;
            font-size: 8px;
        }

        .dispatch-table {
            width: 100%;
            font-size: 8px;
            border-collapse: collapse;
        }

        .dispatch-table td {
            padding: 2px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .dispatch-table td:last-child {
            text-align: right;
            font-weight: 700;
            color: #111;
        }

        .dispatch-table tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            color: #15803d;
            text-transform: uppercase;
            font-size: 7.5px;
        }

        /* Routing */
        .tc-routing {
            border: 1px solid #d1d5db;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 4px;
        }

        .routing-header {
            background: #f3f4f6;
            padding: 2px 6px;
            border-bottom: 1px solid #d1d5db;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #555;
        }

        .routing-body {
            padding: 4px 6px;
            position: relative;
        }

        .route-row {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .route-row+.route-row {
            margin-top: 4px;
        }

        .route-dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .dot-a {
            background: #fff;
            border: 1.5px solid #9ca3af;
            color: #555;
        }

        .dot-b {
            background: #111;
            color: #fff;
        }

        .route-info .route-sub {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #888;
            font-weight: 700;
        }

        .route-info .route-val {
            font-size: 9px;
            font-weight: 600;
            color: #111;
        }

        .route-connector {
            width: 1.5px;
            height: 8px;
            background: #d1d5db;
            margin-left: 8px;
        }

        /* Signatures */
        .tc-sigs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            border-top: 1px solid #d1d5db;
            padding-top: 3px;
        }

        .sig-block {
            text-align: center;
        }

        .sig-block .sig-line {
            border-bottom: 1px solid #333;
            height: 14px;
            margin-bottom: 2px;
        }

        .sig-block .sig-label {
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #333;
        }

        .sig-block .sig-sub {
            font-size: 6.5px;
            color: #888;
            margin-top: 1px;
        }

        /* Copy label */
        .copy-label {
            text-align: right;
            font-size: 6.5px;
            color: #bbb;
            font-style: italic;
            margin-top: 2px;
        }

        /* Toolbar */
        .toolbar {
            background: #111827;
            color: #fff;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 99;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .toolbar .t-left {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 600;
        }

        .toolbar .t-left i {
            color: #60a5fa;
        }

        .toolbar .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.15s;
            margin-left: 8px;
        }

        .btn-gray {
            background: #374151;
            color: #fff;
        }

        .btn-gray:hover {
            background: #4b5563;
        }

        .btn-blue {
            background: #2563eb;
            color: #fff;
        }

        .btn-blue:hover {
            background: #1d4ed8;
        }
    </style>
</head>

<body>

    <div class="toolbar no-print">
        <div class="t-left">
            <i class="fa-solid fa-print"></i>
            <span>Print Preview &mdash; Waybill Ticket (3 Copies / A5)</span>
        </div>
        <div>
            <button onclick="window.close()" class="btn btn-gray">Close Tab</button>
            <button onclick="window.print()" class="btn btn-blue"><i class="fa-solid fa-print" style="margin-right:6px;"></i>Print Now</button>
        </div>
    </div>

    <div class="preview-shell">
        <div class="page-wrapper">

            <?php
            $copies = ['Office Copy', 'Driver Copy', 'Customer Copy'];
            foreach ($copies as $idx => $copyLabel):
            ?>

                <?php if ($idx > 0): ?>
                    <hr class="cut-line"><?php endif; ?>

                <div class="ticket-copy">

                    <!-- Header -->
                    <div class="tc-header">
                        <div class="brand">
                            <img src="../src/ssvLogo.png" alt="SSV Logo">
                            <div class="brand-text">
                                <h1>SSV Trucking</h1>
                                <p>San Leonardo, Nueva Ecija</p>
                                <p>Operations Waybill Record</p>
                            </div>
                        </div>
                        <div class="ticket-id">
                            <div class="label">Waybill Ticket</div>
                            <div class="number"><?= htmlspecialchars($ticket['ticket_number']); ?></div>
                        </div>
                    </div>

                    <!-- Carrier & Dispatch Details -->
                    <div class="tc-body">
                        <div class="info-box">
                            <div class="box-label">Carrier Details</div>
                            <div class="driver-name"><?= htmlspecialchars($ticket['driver_name']); ?></div>
                            <div class="driver-phone"><?= htmlspecialchars($ticket['driver_phone'] ?? 'N/A'); ?></div>
                            <div class="vehicle-row">
                                <span style="color:#666;">Vehicle:</span>
                                <span class="vehicle-badge"><?= htmlspecialchars($ticket['truck_code']); ?></span>
                            </div>
                        </div>

                        <div class="info-box">
                            <div class="box-label">Dispatch Details</div>
                            <table class="dispatch-table">
                                <tbody>
                                    <tr>
                                        <td style="color:#666;">Date Issued:</td>
                                        <td><?= date('M d, Y', strtotime($ticket['dispatch_date'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="color:#666;">Load Volume:</td>
                                        <td><?= number_format($ticket['cubic_meters'] ?? 0, 2); ?> cu.m</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Routing -->
                    <div class="tc-routing">
                        <div class="routing-header">Routing Information</div>
                        <div class="routing-body">
                            <div class="route-row">
                                <div class="route-dot dot-a">A</div>
                                <div class="route-info">
                                    <div class="route-sub">Origin / Loading Point</div>
                                    <div class="route-val"><?= htmlspecialchars($ticket['origin']); ?></div>
                                </div>
                            </div>
                            <div class="route-connector"></div>
                            <div class="route-row">
                                <div class="route-dot dot-b">B</div>
                                <div class="route-info">
                                    <div class="route-sub">Destination / Drop-off</div>
                                    <div class="route-val" style="text-transform:uppercase;text-decoration:underline;"><?= htmlspecialchars($ticket['destination']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Signatures -->
                    <div class="tc-sigs">
                        <div class="sig-block">
                            <div class="sig-line"></div>
                            <div class="sig-label">Driver Signature</div>
                            <div class="sig-sub">Date: ______/______/______</div>
                        </div>
                        <div class="sig-block">
                            <div class="sig-line"></div>
                            <div class="sig-label">Signature</div>
                            <div class="sig-sub">Validated by Customer's Checker</div>
                        </div>
                    </div>

                    <div class="copy-label"><?= $copyLabel ?></div>

                </div>

            <?php endforeach; ?>

        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 800);
        };
    </script>
</body>

</html>