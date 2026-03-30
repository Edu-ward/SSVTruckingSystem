<?php
require './db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['truck_code'])) {
    $truck_code = trim($_POST['truck_code']);

    try {
        // Update the truck's status
        $stmt = $pdo->prepare("UPDATE trucks SET status = 'In Transit' WHERE truck_code = ? AND status = 'Loading'");
        $stmt->execute([$truck_code]);

        // You might also want to update the dispatch ticket status if applicable
        $stmt2 = $pdo->prepare("
            UPDATE dispatches d 
            JOIN trucks t ON d.truck_id = t.id 
            SET d.status = 'In Transit' 
            WHERE t.truck_code = ? AND d.status = 'Pending'
        ");
        $stmt2->execute([$truck_code]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
