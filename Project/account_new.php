<?php
require __DIR__ . '/db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.html'); exit; }
$ym = $_GET['ym'] ?? date('Y-m');
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Add Account</title>
    <link rel="stylesheet" href="styles.css">
  </head>
  <body class="container">
    <div class="card"><div class="card-header"><h2>Add Account</h2></div>
      <div class="card-content">
        <form method="post" action="manage.php">
          <input type="hidden" name="action" value="add_account">
          <input type="hidden" name="ym" value="<?php echo h($ym); ?>">
          <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
          <div class="form-group"><label>Type</label>
            <select name="type" required>
              <option value="">Select Type</option>
              <option>Checking</option><option>Savings</option><option>Credit Card</option><option>Mobile Banking</option>
            </select>
          </div>
          <div class="form-group"><label>Balance (৳)</label><input type="number" step="0.01" name="balance" value="0" required></div>
          <div class="form-actions"><a class="btn-secondary" href="dashboard.php?ym=<?php echo h($ym); ?>">Cancel</a><button class="btn-primary" type="submit">Add</button></div>
        </form>
      </div>
    </div>
  </body>
</html>
