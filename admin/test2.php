<?php
require '../db.php';
$allDispatches = $pdo->query("SELECT d.id, d.ticket_number, t.truck_code, CONCAT(dr.first_name, ' ', dr.last_name) AS driver_name, d.status, d.destination, d.created_at, d.transit_start_time, d.transit_end_time FROM dispatches d JOIN trucks t ON d.truck_id = t.id JOIN drivers dr ON d.driver_id = dr.id ORDER BY d.id DESC")->fetchAll(PDO::FETCH_ASSOC);
$completedTickets = array_filter($allDispatches, function ($d) {
    return $d['status'] == 'Delivered';
});
echo "Count all: " . count($allDispatches) . "\n";
echo "Count completed: " . count($completedTickets) . "\n";
