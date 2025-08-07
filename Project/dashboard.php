<?php

require 'db.php';


function sanitize($data) {
    return htmlspecialchars(trim($data));
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $type   = $_POST['type'] ?? '';    
    $table  = $type === 'expense' ? 'expenses' : 'incomes';

   
    if ($action === 'create') {
        $category = sanitize($_POST['category']);
        $amount   = (float)$_POST['amount'];
        $stmt = $conn->prepare("INSERT INTO $table (category, amount) VALUES (?, ?)");
        $stmt->bind_param('sd', $category, $amount);
        $stmt->execute();
        $stmt->close();
    }

    
    if ($action === 'update') {
        $id       = (int)$_POST['id'];
        $category = sanitize($_POST['category']);
        $amount   = (float)$_POST['amount'];
        $stmt = $conn->prepare("UPDATE $table SET category=?, amount=? WHERE id=?");
        $stmt->bind_param('sdi', $category, $amount, $id);
        $stmt->execute();
        $stmt->close();
    }

    
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM $table WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    
    $sumIncome = $conn->query("SELECT IFNULL(SUM(amount),0) AS total FROM incomes")->fetch_assoc()['total'];
    $sumExpense = $conn->query("SELECT IFNULL(SUM(amount),0) AS total FROM expenses")->fetch_assoc()['total'];
    $currentSavings = $sumIncome - $sumExpense;

    $stmt = $conn->prepare("INSERT INTO savings (amount) VALUES (?)");
    $stmt->bind_param('d', $currentSavings);
    $stmt->execute();
    $stmt->close();
}


$incomeQuery  = $conn->query("SELECT * FROM incomes ORDER BY created_at DESC");
$expenseQuery = $conn->query("SELECT * FROM expenses ORDER BY created_at DESC");


$latestSavingsRes = $conn->query("SELECT amount, calculated_at FROM savings ORDER BY calculated_at DESC LIMIT 1");
$latestSavingsRow = $latestSavingsRes->fetch_assoc();
$latestSavings = $latestSavingsRow['amount'] ?? 0;


$savingsHistory = $conn->query("SELECT amount, calculated_at FROM savings ORDER BY calculated_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Manager with Savings</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0 auto; max-width: 1000px; padding: 20px; }
        h2 { margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        form { margin-bottom: 15px; }
        .section { margin-bottom: 40px; }
        .savings { background: #f1fdf1; padding: 15px; border: 1px solid #d4eed4; border-radius: 5px; }
    </style>
</head>
<body>
<h1>Finance Manager</h1>

<div class="savings">
    <h2>Current Savings</h2>
    <p><strong>৳<?= number_format($latestSavings, 2); ?></strong> (Income − Expense)</p>
    <h3>Savings History (latest 10)</h3>
    <table>
        <tr><th>Date</th><th>Amount</th></tr>
        <?php while ($row = $savingsHistory->fetch_assoc()): ?>
        <tr>
            <td><?= $row['calculated_at']; ?></td>
            <td><?= number_format($row['amount'], 2); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>


<div class="section">
    <h2>Manage Income</h2>
  
    <form method="post">
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="type" value="income">
        <input type="text" name="category" placeholder="Income category" required>
        <input type="number" step="0.01" name="amount" placeholder="Amount" required>
        <button type="submit">Add Income</button>
    </form>
    
    <table>
        <tr><th>ID</th><th>Category</th><th>Amount</th><th>Date</th><th>Actions</th></tr>
        <?php while ($row = $incomeQuery->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['category']); ?></td>
            <td><?= $row['amount']; ?></td>
            <td><?= $row['created_at']; ?></td>
            <td>
                
                <form method="post" style="display:inline">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="type" value="income">
                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                    <input type="text" name="category" value="<?= htmlspecialchars($row['category']); ?>" required>
                    <input type="number" step="0.01" name="amount" value="<?= $row['amount']; ?>" required>
                    <button type="submit">Update</button>
                </form>
                
                <form method="post" style="display:inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="type" value="income">
                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                    <button type="submit" onclick="return confirm('Delete this income?');">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>


<div class="section">
    <h2>Manage Expense</h2>
    
    <form method="post">
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="type" value="expense">
        <input type="text" name="category" placeholder="Expense category" required>
        <input type="number" step="0.01" name="amount" placeholder="Amount" required>
        <button type="submit">Add Expense</button>
    </form>
    
    <table>
        <tr><th>ID</th><th>Category</th><th>Amount</th><th>Date</th><th>Actions</th></tr>
        <?php while ($row = $expenseQuery->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['category']); ?></td>
            <td><?= $row['amount']; ?></td>
            <td><?= $row['created_at']; ?></td>
            <td>
                
                <form method="post" style="display:inline">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="type" value="expense">
                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                    <input type="text" name="category" value="<?= htmlspecialchars($row['category']); ?>" required>
                    <input type="number" step="0.01" name="amount" value="<?= $row['amount']; ?>" required>
                    <button type="submit">Update</button>
                </form>
                
                <form method="post" style="display:inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="type" value="expense">
                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                    <button type="submit" onclick="return confirm('Delete this expense?');">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
<?php
$incomeQuery->close();
$expenseQuery->close();
$savingsHistory->close();
$conn->close();
?>
