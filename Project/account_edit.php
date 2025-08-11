<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.html'); exit; }
$user_id = (int)$_SESSION['user_id'];
$ym = $_GET['ym'] ?? date('Y-m');
$id = (int)($_GET['id'] ?? 0);

$s = mysqli_prepare($conn, "SELECT id, name, type, balance FROM accounts WHERE id=? AND user_id=?");
mysqli_stmt_bind_param($s, 'ii', $id, $user_id);
mysqli_stmt_execute($s);
$acc = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
if (!$acc) { http_response_code(404); exit('Account not found'); }
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Edit Account</title><link rel="stylesheet" href="styles.css"></head>
<body class="container">
  <div class="card"><div class="card-header"><h2>Edit Account</h2></div>
    <div class="card-content">
      <form method="post" action="manage.php">
        <input type="hidden" name="action" value="update_account">
        <input type="hidden" name="id" value="<?php echo (int)$acc['id']; ?>">
        <input type="hidden" name="ym" value="<?php echo h($ym); ?>">
        <div class="form-group"><label>Name</label><input type="text" name="name" required value="<?php echo h($acc['name']); ?>"></div>
        <div class="form-group"><label>Type</label>
          <select name="type" required>
            <?php $types=['Checking','Savings','Credit Card','Mobile Banking']; foreach($types as $t){ $sel=$t===$acc['type']?'selected':''; echo "<option $sel>".h($t)."</option>"; } ?>
          </select>
        </div>
        <div class="form-group"><label>Balance (৳)</label><input type="number" step="0.01" name="balance" required value="<?php echo h($acc['balance']); ?>"></div>
        <div class="form-actions"><a class="btn-secondary" href="dashboard.php?ym=<?php echo h($ym); ?>">Cancel</a><button class="btn-primary" type="submit">Save</button></div>
      </form>
    </div>
  </div>
</body></html>
