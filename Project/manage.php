<?php
require __DIR__ . '/db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.html'); exit; }
$user_id = (int)$_SESSION['user_id'];

$action = $_POST['action'] ?? '';
$ym = $_POST['ym'] ?? date('Y-m');

function belongs_account($conn, $user_id, $acc_id){
  $s = mysqli_prepare($conn, "SELECT id FROM accounts WHERE id=? AND user_id=?");
  mysqli_stmt_bind_param($s, 'ii', $acc_id, $user_id);
  mysqli_stmt_execute($s);
  return (bool)mysqli_fetch_assoc(mysqli_stmt_get_result($s));
}

try {
  switch ($action) {
    case 'set_income':
      $income = (float)($_POST['income'] ?? 0);
      $s = mysqli_prepare($conn, "INSERT INTO monthly_incomes (user_id, ym, income) VALUES (?,?,?) ON DUPLICATE KEY UPDATE income=VALUES(income)");
      mysqli_stmt_bind_param($s, 'isd', $user_id, $ym, $income);
      mysqli_stmt_execute($s);
      set_flash('Monthly income saved.');
      break;

    case 'add_account':
      $name = trim($_POST['name'] ?? '');
      $type = trim($_POST['type'] ?? '');
      $balance = (float)($_POST['balance'] ?? 0);
      if ($name === '' || $type === '') { set_flash('Account fields missing','error'); break; }
      $s = mysqli_prepare($conn, "INSERT INTO accounts (user_id, name, type, balance) VALUES (?,?,?,?)");
      mysqli_stmt_bind_param($s, 'issd', $user_id, $name, $type, $balance);
      mysqli_stmt_execute($s);
      set_flash('Account added.');
      break;

    case 'update_account':
      $id = (int)($_POST['id'] ?? 0);
      $name = trim($_POST['name'] ?? '');
      $type = trim($_POST['type'] ?? '');
      $balance = (float)($_POST['balance'] ?? 0);
      if (!$id || $name === '' || $type === '') { set_flash('Account fields missing','error'); break; }
      $s = mysqli_prepare($conn, "UPDATE accounts SET name=?, type=?, balance=? WHERE id=? AND user_id=?");
      mysqli_stmt_bind_param($s, 'ssdii', $name, $type, $balance, $id, $user_id);
      mysqli_stmt_execute($s);
      set_flash('Account updated.');
      break;

    case 'delete_account':
      $id = (int)($_POST['id'] ?? 0);
      if (!$id) { set_flash('Account id missing','error'); break; }
      $s = mysqli_prepare($conn, "DELETE FROM accounts WHERE id=? AND user_id=?");
      mysqli_stmt_bind_param($s, 'ii', $id, $user_id);
      mysqli_stmt_execute($s);
      set_flash('Account deleted.');
      break;

    case 'add_expense':
      $desc = trim($_POST['description'] ?? '');
      $amount = (float)($_POST['amount'] ?? 0);
      $category = trim($_POST['category'] ?? '');
      $date = $_POST['date'] ?? ($ym.'-01');
      $account_id = isset($_POST['account_id']) && $_POST['account_id'] !== '' ? (int)$_POST['account_id'] : null;
      if ($desc === '' || $amount <= 0 || $category === '') { set_flash('Expense fields missing','error'); break; }
      if ($account_id !== null && !belongs_account($conn, $user_id, $account_id)) $account_id = null;
      $neg = -abs($amount);
      if ($account_id === null) {
        $s = mysqli_prepare($conn, "INSERT INTO transactions (user_id, description, amount, category, date, account_id) VALUES (?,?,?,?,?, NULL)");
        mysqli_stmt_bind_param($s, 'isdss', $user_id, $desc, $neg, $category, $date);
      } else {
        $s = mysqli_prepare($conn, "INSERT INTO transactions (user_id, description, amount, category, date, account_id) VALUES (?,?,?,?,?,?)");
        mysqli_stmt_bind_param($s, 'isdssi', $user_id, $desc, $neg, $category, $date, $account_id);
      }
      mysqli_stmt_execute($s);
      set_flash('Expense added.');
      break;

    case 'update_expense':
      $id = (int)($_POST['id'] ?? 0);
      $desc = trim($_POST['description'] ?? '');
      $amount = (float)($_POST['amount'] ?? 0);
      $category = trim($_POST['category'] ?? '');
      $date = $_POST['date'] ?? ($ym.'-01');
      $account_id = isset($_POST['account_id']) && $_POST['account_id'] !== '' ? (int)$_POST['account_id'] : null;
      if (!$id || $desc === '' || $amount <= 0 || $category === '') { set_flash('Expense fields missing','error'); break; }
      if ($account_id !== null && !belongs_account($conn, $user_id, $account_id)) $account_id = null;
      $neg = -abs($amount);
      if ($account_id === null) {
        $s = mysqli_prepare($conn, "UPDATE transactions SET description=?, amount=?, category=?, date=?, account_id=NULL WHERE id=? AND user_id=?");
        mysqli_stmt_bind_param($s, 'sdssii', $desc, $neg, $category, $date, $id, $user_id);
      } else {
        $s = mysqli_prepare($conn, "UPDATE transactions SET description=?, amount=?, category=?, date=?, account_id=? WHERE id=? AND user_id=?");
        mysqli_stmt_bind_param($s, 'sdsssii', $desc, $neg, $category, $date, $account_id, $id, $user_id);
      }
      mysqli_stmt_execute($s);
      set_flash('Expense updated.');
      break;

    case 'delete_expense':
      $id = (int)($_POST['id'] ?? 0);
      if (!$id) { set_flash('Expense id missing','error'); break; }
      $s = mysqli_prepare($conn, "DELETE FROM transactions WHERE id=? AND user_id=?");
      mysqli_stmt_bind_param($s, 'ii', $id, $user_id);
      mysqli_stmt_execute($s);
      set_flash('Expense deleted.');
      break;
  }
} catch (Throwable $e) {
  set_flash('Server error: '.$e->getMessage(),'error');
}

header('Location: dashboard.php?ym=' . urlencode($ym));
exit;
