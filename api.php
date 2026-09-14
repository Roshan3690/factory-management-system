<?php
header('Content-Type: application/json');
require 'db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Create/Update
    if ($action === 'add_work') {
        $description = $_POST['description'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        $work_date = $_POST['work_date'] ?? date('Y-m-d');
        $job_name = $_POST['job_name'] ?? '';
        $category = $_POST['category'] ?? 'General';
        $customer_name = $_POST['customer_name'] ?? '';
        
        $stmt = $pdo->prepare("INSERT INTO works (description, amount, work_date, job_name, category, customer_name, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, 'Pending', 'Pending')");
        if ($stmt->execute([$description, $amount, $work_date, $job_name, $category, $customer_name])) {
            echo json_encode(['status' => 'success', 'message' => 'Work added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add work']);
        }
    } elseif ($action === 'update_payment') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? 'Pending'; // 'Paid' or 'Pending'
        
        $stmt = $pdo->prepare("UPDATE works SET payment_status = ? WHERE id = ?");
        if ($stmt->execute([$status, $id])) {
             echo json_encode(['status' => 'success', 'message' => 'Payment status updated']);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
        }
    } elseif ($action === 'update_job_payment') {
        $job_name = $_POST['job_name'] ?? '';
        $status = $_POST['status'] ?? 'Paid';
        
        $stmt = $pdo->prepare("UPDATE works SET payment_status = ? WHERE job_name = ?");
        if ($stmt->execute([$status, $job_name])) {
             echo json_encode(['status' => 'success', 'message' => 'Job payment status updated']);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
        }
    } elseif ($action === 'update_work_details') {
        $id = $_POST['id'];
        $description = $_POST['description'];
        $amount = $_POST['amount'];
        $category = $_POST['category'];
        
        $stmt = $pdo->prepare("UPDATE works SET description = ?, amount = ?, category = ? WHERE id = ?");
        if ($stmt->execute([$description, $amount, $category, $id])) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    } elseif ($action === 'update_customer') {
        $name = $_POST['name'] ?? '';
        $address = $_POST['address'] ?? '';
        $gstin = $_POST['gstin'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        $stmt = $pdo->prepare("INSERT INTO customers (name, address, gstin, phone) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE address=VALUES(address), gstin=VALUES(gstin), phone=VALUES(phone)");
        if ($stmt->execute([$name, $address, $gstin, $phone])) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle Read
    if ($action === 'get_works') {
        $stmt = $pdo->query("SELECT * FROM works ORDER BY work_date DESC");
        $works = $stmt->fetchAll();
        echo json_encode($works);
    } elseif ($action === 'get_jobs') {
        $stmt = $pdo->query("SELECT job_name, customer_name, SUM(amount) as total_amount, 
                             COUNT(*) as item_count, 
                             SUM(CASE WHEN payment_status = 'Pending' THEN amount ELSE 0 END) as pending_amount
                             FROM works 
                             WHERE job_name != '' 
                             GROUP BY job_name 
                             ORDER BY MAX(work_date) DESC");
        $jobs = $stmt->fetchAll();
        echo json_encode($jobs);
    } elseif ($action === 'get_companies') {
        $stmt = $pdo->query("
            SELECT w.customer_name, COUNT(DISTINCT w.job_name) as job_count, SUM(w.amount) as total_spent,
                   c.address, c.gstin, c.phone
            FROM works w
            LEFT JOIN customers c ON w.customer_name = c.name
            WHERE w.customer_name != '' 
            GROUP BY w.customer_name 
            ORDER BY w.customer_name
        ");
        $companies = $stmt->fetchAll();
        echo json_encode($companies);
    } elseif ($action === 'get_job_details') {
        $job_name = $_GET['job_name'] ?? '';
        $stmt = $pdo->prepare("SELECT * FROM works WHERE job_name = ? ORDER BY work_date DESC");
        $stmt->execute([$job_name]);
        $works = $stmt->fetchAll();
        echo json_encode($works);
    } elseif ($action === 'get_company_works') {
        $customer_name = $_GET['customer'] ?? '';
        $stmt = $pdo->prepare("SELECT * FROM works WHERE customer_name = ? AND payment_status = 'Pending' ORDER BY work_date DESC");
        $stmt->execute([$customer_name]);
        $works = $stmt->fetchAll();
        echo json_encode($works);
    } elseif ($action === 'get_suggestions') {
        $stmtCust = $pdo->query("SELECT DISTINCT customer_name FROM works WHERE customer_name IS NOT NULL AND customer_name != '' ORDER BY customer_name");
        $customers = $stmtCust->fetchAll(PDO::FETCH_COLUMN);

        $stmtJob = $pdo->query("SELECT DISTINCT job_name FROM works WHERE job_name IS NOT NULL AND job_name != '' ORDER BY job_name");
        $jobs = $stmtJob->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode(['customers' => $customers, 'jobs' => $jobs]);
    } elseif ($action === 'get_stats') {
         $stmt = $pdo->query("SELECT SUM(amount) as total_pending FROM works WHERE payment_status = 'Pending'");
         $pending = $stmt->fetch()['total_pending'] ?? 0;
         
         $stmt = $pdo->query("SELECT COUNT(*) as completed_count FROM works WHERE status = 'Done'");
         $completed = $stmt->fetch()['completed_count'] ?? 0;
         
         echo json_encode(['total_pending' => $pending, 'completed_jobs' => $completed]);
    }
}
?>
