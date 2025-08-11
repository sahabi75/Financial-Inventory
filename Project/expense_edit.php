<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.html'); exit; }
$user_id = (int)$_SESSION['user_id'];
$ym = $_GET['ym'] ?? date('Y-m');
$id = (int)($_GET['id'] ?? 0);

$txs = mysqli_prepare($conn, "SELECT id, description, amount, category, date, account_id FROM transactions WHERE id=? AND user_id=?");
mysqli_stmt_bind_param($txs, 'ii', $id, $user_id);
mysqli_stmt_execute($txs);
$tx = mysqli_fetch_assoc(mysqli_stmt_get_result($txs));
if (!$tx) { http_response_code(404); exit('Expense not found'); }

$st = mysqli_prepare($conn, "SELECT id, name, type FROM accounts WHERE user_id=? ORDER BY name ASC");
mysqli_stmt_bind_param($st, 'i', $user_id);
mysqli_stmt_execute($st);
$accounts = mysqli_fetch_all(mysqli_stmt_get_result($st), MYSQLI_ASSOC);

$pos_amount = abs((float)$tx['amount']); // stored negative
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Edit Expense</title><link rel="stylesheet" href="styles.css"></head>
<body class="container">
  <div class="card"><div class="card-header"><h2>Edit Expense</h2></div>
    <div class="card-content">
      <form method="post" action="manage.php">
        <input type="hidden" name="action" value="update_expense">
        <input type="hidden" name="id" value="<?php echo (int)$tx['id']; ?>">
        <input type="hidden" name="ym" value="<?php echo h($ym); ?>">
        <div class="form-group"><label>Description</label><input type="text" name="description" required value="<?php echo h($tx['description']); ?>"></div>
        <div class="form-group"><label>Amount (৳)</label><input type="number" step="0.01" name="amount" required value="<?php echo h($pos_amount); ?>"></div>
        <div class="form-group"><label>Category</label><input type="text" name="category" required value="<?php echo h($tx['category']); ?>"></div>
        <div class="form-group"><label>Account</label>
          <select name="account_id">
            <option value="">(No account)</option>
            <?php foreach ($accounts as $a): $sel = ($tx['account_id']==$a['id'])?'selected':''; ?>
              <option value="<?php echo (int)$a['id']; ?>" <?php echo $sel; ?>><?php echo h($a['name'].' — '.$a['type']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label>Date</label><input type="date" name="date" required value="<?php echo h($tx['date']); ?>"></div>
        <div class="form-actions"><a class="btn-secondary" href="dashboard.php?ym=<?php echo h($ym); ?>">Cancel</a><button class="btn-primary" type="submit">Save</button></div>
      </form>
    </div>
  </div>
</body></html>
