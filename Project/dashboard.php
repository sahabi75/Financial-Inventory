<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.html'); exit; }
$user_id = (int)$_SESSION['user_id'];

$ym = $_GET['ym'] ?? '2025-08';
if (!preg_match('/^\d{4}-\d{2}$/', $ym)) $ym = date('Y-m');
$from = $ym.'-01';
$to = date('Y-m-t', strtotime($from));

$fname = $_SESSION['fname'] ?? '';
$lname = $_SESSION['lname'] ?? '';
$email = $_SESSION['email'] ?? '';
$gender= $_SESSION['gender'] ?? '';
$age    = $_SESSION['age'] ?? '';

$st = mysqli_prepare($conn, "SELECT id, name, type, balance FROM accounts WHERE user_id=? ORDER BY id DESC");
mysqli_stmt_bind_param($st, 'i', $user_id);
mysqli_stmt_execute($st);
$accounts = mysqli_fetch_all(mysqli_stmt_get_result($st), MYSQLI_ASSOC);

$total_balance = 0; foreach($accounts as $a) $total_balance += (float)$a['balance'];

$si = mysqli_prepare($conn, "SELECT income FROM monthly_incomes WHERE user_id=? AND ym=?");
mysqli_stmt_bind_param($si, 'is', $user_id, $ym);
mysqli_stmt_execute($si);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($si));
$monthly_income = (float)($row['income'] ?? 0);

$tx = mysqli_prepare($conn, "SELECT t.id, t.description, t.amount, t.category, t.date, t.account_id, a.name AS account_name
                              FROM transactions t LEFT JOIN accounts a ON a.id=t.account_id
                              WHERE t.user_id=? AND t.date BETWEEN ? AND ? AND t.amount < 0
                              ORDER BY t.date DESC, t.id DESC");
mysqli_stmt_bind_param($tx, 'iss', $user_id, $from, $to);
mysqli_stmt_execute($tx);
$expenses = mysqli_fetch_all(mysqli_stmt_get_result($tx), MYSQLI_ASSOC);

$monthly_expenses = 0; foreach($expenses as $e) $monthly_expenses += abs((float)$e['amount']);
$monthly_savings = $monthly_income - $monthly_expenses;

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard </title>
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
          <input type="month" name="ym" value="<?php echo h($ym); ?>">
          <button class="btn-secondary" type="submit">Change</button>
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
    <p><strong>Age:</strong><span><?php echo h($age); ?></span></p>
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
    <div class="card"><div class="card-content"><div class="card-info"><h3>Monthly Savings</h3><p class="amount"><?php echo bdt($monthly_savings); ?></p></div><div class="card-icon savings"><i class="fas fa-piggy-bank"></i></div></div></div>
  </div>

  <div class="dashboard-layout">
    <div class="left-column">
      <div class="card">
        <div class="card-header">
          <h2>Account Overview</h2>
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
