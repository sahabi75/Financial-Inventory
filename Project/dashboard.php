<?php
require __DIR__ . '/db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.html'); exit; }
$user_id = (int)$_SESSION['user_id'];

$ym = $_GET['ym'] ?? '2025-08';
if (!preg_match('/^\d{4}-\d{2}$/', $ym)) $ym = date('Y-m');
$from = $ym.'-01';
$to = date('Y-m-t', strtotime($from));

$q = isset($_GET['q']) ? trim($_GET['q']) : '';


$fname = $_SESSION['fname'] ?? '';
$lname = $_SESSION['lname'] ?? '';
$email = $_SESSION['email'] ?? '';
$gender= $_SESSION['gender'] ?? '';


$age = $_SESSION['age'] ?? null;

// Try to fetch age (or DOB) from database users table
$u = mysqli_prepare($conn, "SELECT age FROM users WHERE id=? LIMIT 1");
if ($u) {
  mysqli_stmt_bind_param($u, 'i', $user_id);
  mysqli_stmt_execute($u);
  $urow = mysqli_fetch_assoc(mysqli_stmt_get_result($u));
  if ($urow) {
    if (isset($urow['age']) && $urow['age'] !== '' && $urow['age'] !== null) {
      $age = (int)$urow['age'];
    } else {
      $dob = $urow['dob'] ?? ($urow['birthdate'] ?? null);
      if ($dob) {
        $ts = strtotime($dob);
        if ($ts !== false) {
          $age = (int) floor( (time() - $ts) / (365.2425*24*3600) );
        }
      }
    }
  }
}


$acc_sql = "SELECT id, name, type, balance FROM accounts WHERE user_id=?";
$acc_bind_types = "i";
$acc_bind_vals = [$user_id];

if ($q !== '') {
  $acc_sql .= " AND (name LIKE CONCAT('%', ?, '%') OR type LIKE CONCAT('%', ?, '%'))";
  $acc_bind_types .= "ss";
  $acc_bind_vals[] = $q;
  $acc_bind_vals[] = $q;
}

$acc_sql .= " ORDER BY id DESC";

$st = mysqli_prepare($conn, $acc_sql);
mysqli_stmt_bind_param($st, $acc_bind_types, ...$acc_bind_vals);
mysqli_stmt_execute($st);
$accounts = mysqli_fetch_all(mysqli_stmt_get_result($st), MYSQLI_ASSOC);
$tb = mysqli_prepare($conn, "SELECT COALESCE(SUM(balance),0) FROM accounts WHERE user_id=?");
mysqli_stmt_bind_param($tb, 'i', $user_id);
mysqli_stmt_execute($tb);
$total_balance = (float) mysqli_fetch_row(mysqli_stmt_get_result($tb))[0];

$si = mysqli_prepare($conn, "SELECT income FROM monthly_incomes WHERE user_id=? AND ym=?");
mysqli_stmt_bind_param($si, 'is', $user_id, $ym);
mysqli_stmt_execute($si);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($si));
$monthly_income = (float)($row['income'] ?? 0);

$tx_sql = "SELECT t.id, t.description, t.amount, t.category, t.date, t.account_id, a.name AS account_name
           FROM transactions t LEFT JOIN accounts a ON a.id=t.account_id
           WHERE t.user_id=? AND t.date BETWEEN ? AND ? AND t.amount < 0";
$bind_types = "iss";
$bind_vals = [$user_id, $from, $to];
if ($q !== '') {
  $tx_sql .= " AND (t.description LIKE CONCAT('%', ?, '%') OR t.category LIKE CONCAT('%', ?, '%') OR a.name LIKE CONCAT('%', ?, '%'))";
  $bind_types .= "sss";
  $bind_vals[] = $q; $bind_vals[] = $q; $bind_vals[] = $q;
}
$tx_sql .= " ORDER BY t.date DESC, t.id DESC";
$tx = mysqli_prepare($conn, $tx_sql);
mysqli_stmt_bind_param($tx, $bind_types, ...$bind_vals);
mysqli_stmt_execute($tx);
$expenses = mysqli_fetch_all(mysqli_stmt_get_result($tx), MYSQLI_ASSOC);
$ex = mysqli_prepare($conn, "SELECT COALESCE(SUM(ABS(amount)),0) FROM transactions WHERE user_id=? AND date BETWEEN ? AND ? AND amount < 0");
mysqli_stmt_bind_param($ex, 'iss', $user_id, $from, $to);
mysqli_stmt_execute($ex);
$monthly_expenses = (float) mysqli_fetch_row(mysqli_stmt_get_result($ex))[0];
$monthly_savings = $monthly_income - $monthly_expenses;

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard</title>
  <link rel="stylesheet" href="dash.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<header class="header">
  <div class="container">
    <div class="header-content">
      <div class="logo"><h1><i class="fas fa-chart-line"></i> FinanceTracker</h1></div>
      <div class="header-actions">
        <form method="get" action="dashboard.php" style="display:flex;gap:.5rem;align-items:center;">
          <input type="month" name="ym" value="<?php echo h($ym); ?>" />
          <input type="text" name="q" value="<?php echo h($q); ?>" placeholder="Search accounts, description, category" style="width:16rem;">
          <button class="btn-secondary" type="submit"><i class="fas fa-search"></i> Apply</button>
          <?php if ($q !== ''): ?><a class="btn-secondary" href="dashboard.php?ym=<?php echo urlencode($ym); ?>">Clear</a><?php endif; ?>
        </form>
        <form method="post" action="manage.php" style="display:flex;gap:.5rem;align-items:center;margin-left:.5rem;">
          <input type="hidden" name="action" value="set_income">
          <input type="hidden" name="ym" value="<?php echo h($ym); ?>">
          <input name="income" type="number" step="0.01" value="<?php echo h($monthly_income); ?>" style="width:10rem" placeholder="Monthly income">
          <button class="btn-secondary" type="submit"><i class="fas fa-sack-dollar"></i> Save</button>
        </form>
        <a class="btn-secondary" href="logout.php"><i class="fas fa-right-from-bracket"></i></a>
      </div>
    </div>
  </div>
</header>

<div class="personal-info card">
  <div class="card-header"><h2>User Information</h2></div>
  <div class="card-content" id="userInfo">
    <p><strong>Name:</strong> <span><?php echo h(trim(($fname.' '.$lname)) ?: ($_SESSION['username'] ?? '')); ?></span></p>
    <p><strong>Age:</strong> <span><?php echo ($age !== null && $age !== '') ? h($age) : '—'; ?></span></p>
    <p><strong>Email:</strong> <span><?php echo h($email); ?></span></p>
    <p><strong>Gender:</strong> <span><?php echo h($gender); ?></span></p>
  </div>
</div>

<div class="container">
  <?php if ($flash): ?>
  <div class="card"><div class="card-content"><strong><?php echo h(ucfirst($flash['t'])); ?>:</strong> <?php echo h($flash['m']); ?></div></div>
  <?php endif; ?>

  <div class="summary-cards">
    <div class="card"><div class="card-content"><div class="card-info"><h3>Total Balance</h3><p class="amount"><?php echo bdt($total_balance); ?></p></div><div class="card-icon wallet"><i class="fas fa-wallet"></i></div></div></div>
    <div class="card"><div class="card-content"><div class="card-info"><h3>Monthly Income</h3><p class="amount"><?php echo bdt($monthly_income); ?></p></div><div class="card-icon income"><i class="fas fa-arrow-up"></i></div></div></div>
    <div class="card"><div class="card-content"><div class="card-info"><h3>Monthly Expenses</h3><p class="amount"><?php echo bdt($monthly_expenses); ?></p></div><div class="card-icon expense"><i class="fas fa-arrow-down"></i></div></div></div>
    <div class="card"><div class="card-content"><div class="card-info"><h3>Balance remaining after expense</h3><p class="amount"><?php echo bdt($monthly_savings); ?></p></div><div class="card-icon savings"><i class="fas fa-piggy-bank"></i></div></div></div>
  </div>

  <div class="dashboard-layout">
    <div class="left-column">
      <div class="card">
        <div class="card-header">
          <h2>Balance Overview</h2>
          <a class="btn-primary" href="account_new.php?ym=<?php echo h($ym); ?>"><i class="fas fa-plus"></i> Add Account</a>
        </div>
        <div class="accounts-list">
          <?php if (!count($accounts)): ?>
            <div class="empty-state">
              <i class="fas fa-university"></i>
              <h3>No accounts added yet</h3>
              <p>Add your first account to get started</p>
              <a class="btn-primary" href="account_new.php?ym=<?php echo h($ym); ?>"><i class="fas fa-plus"></i> Add Account</a>
            </div>
          <?php else: foreach($accounts as $a): ?>
            <div class="account-item">
              <div class="account-info">
                <div class="account-color" style="background:#2563eb"></div>
                <div class="account-details"><h4><?php echo h($a['name']); ?></h4><p><?php echo h($a['type']); ?></p></div>
              </div>
              <div>
                <a class="btn-link" href="account_edit.php?id=<?php echo (int)$a['id']; ?>&ym=<?php echo h($ym); ?>"><i class="fas fa-pen"></i> Edit</a>
                <form method="post" action="manage.php" style="display:inline">
                  <input type="hidden" name="action" value="delete_account">
                  <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
                  <input type="hidden" name="ym" value="<?php echo h($ym); ?>">
                  <button class="btn-link" type="submit"><i class="fas fa-trash"></i> Delete</button>
                </form>
              </div>
              <div class="account-balance <?php echo ((float)$a['balance']>=0?'positive':'negative'); ?>"><?php echo bdt($a['balance']); ?></div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>

    <div class="right-column">
      <div class="card">
        <div class="card-header">
          <h2>Expenses (<?php echo h($ym); ?>)</h2>
          <a class="btn-secondary" href="expense_new.php?ym=<?php echo h($ym); ?>"><i class="fas fa-plus"></i> Add Expense</a>
        </div>
        <div class="transactions-list">
          <?php if (!count($expenses)): ?>
            <div class="empty-state">
              <i class="fas fa-receipt"></i>
              <h3>No expenses for this month</h3>
              <p>Add your first expense to track spending</p>
              <a class="btn-primary" href="expense_new.php?ym=<?php echo h($ym); ?>"><i class="fas fa-plus"></i> Add Expense</a>
            </div>
          <?php else: foreach($expenses as $t): ?>
            <div class="transaction-item">
              <div class="transaction-info">
                <h4><?php echo h($t['description']); ?></h4>
                <div class="transaction-meta"><?php echo h($t['category']); ?> • <?php echo date('M j', strtotime($t['date'])); ?><?php if (!empty($t['account_name'])): ?> • <?php echo h($t['account_name']); ?><?php endif; ?></div>
              </div>
              <div>
                <a class="btn-link" href="expense_edit.php?id=<?php echo (int)$t['id']; ?>&ym=<?php echo h($ym); ?>"><i class="fas fa-pen"></i> Edit</a>
                <form method="post" action="manage.php" style="display:inline">
                  <input type="hidden" name="action" value="delete_expense">
                  <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
                  <input type="hidden" name="ym" value="<?php echo h($ym); ?>">
                  <button class="btn-link" type="submit"><i class="fas fa-trash"></i> Delete</button>
                </form>
              </div>
              <div class="transaction-amount negative"><?php echo bdt(abs((float)$t['amount'])); ?></div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
