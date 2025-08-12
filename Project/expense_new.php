<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.html'); exit; }
$user_id = (int)$_SESSION['user_id'];
$ym = $_GET['ym'] ?? date('Y-m');

$st = mysqli_prepare($conn, "SELECT id, name, type FROM accounts WHERE user_id=? ORDER BY name ASC");
mysqli_stmt_bind_param($st, 'i', $user_id);
mysqli_stmt_execute($st);
$accounts = mysqli_fetch_all(mysqli_stmt_get_result($st), MYSQLI_ASSOC);

$default_date = date('Y-m-d');
if (substr($default_date,0,7) != $ym) $default_date = $ym . '-01';
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Add Expense</title>
    <link rel="stylesheet" href="styles.css">
  </head>
  <body class="container">
    <div class="card"><div class="card-header"><h2>Add Expense</h2></div>
      <div class="card-content">
        <form method="post" action="manage.php">
          <input type="hidden" name="action" value="add_expense">
          <input type="hidden" name="ym" value="<?php echo h($ym); ?>">
          <div class="form-group"><label>Description</label><input type="text" name="description" required placeholder="e.g., Rent"></div>
          <div class="form-group"><label>Amount (৳)</label><input type="number" step="0.01" name="amount" required placeholder="30000"></div>
          <div class="form-group"><label>Category</label><input type="text" name="category" required placeholder="e.g., Rent, Food"></div>
          <div class="form-group"><label>Account</label>
            <select name="account_id">
              <option value="">(No account)</option>
              <?php foreach($accounts as $a): ?>
                <option value="<?php echo (int)$a['id']; ?>"><?php echo h($a['name'].' — '.$a['type']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Date</label><input type="date" name="date" value="<?php echo h($default_date); ?>" required></div>
          <div class="form-actions"><a class="btn-secondary" href="dashboard.php?ym=<?php echo h($ym); ?>">Cancel</a><button class="btn-primary" type="submit">Add</button></div>
        </form>
      </div>
    </div>
  </body>
</html>
