
let accounts = [];
let expenses = [];
let transactions = [];
let income = 0;


function updateUI() {
  const totalExpenses = expenses.reduce((sum, e) => sum + e.amount, 0);
  const balance = income - totalExpenses;
  const savings = income > 0 ? balance : 0;

  document.getElementById("totalBalance").textContent = `৳${balance}`;
  document.getElementById("monthlyIncome").textContent = `৳${income}`;
  document.getElementById("monthlyExpenses").textContent = `৳${totalExpenses}`;
  document.getElementById("monthlySavings").textContent = `৳${savings}`;

  renderAccounts();
  renderExpenseChart();
  renderTransactions();
}


function showAddAccountModal() {
  document.getElementById("addAccountModal").style.display = "block";
}

function addAccount(name, type, balance) {
  accounts.push({ name, type, balance });
  updateUI();
}

function renderAccounts() {
  const container = document.getElementById("accountsList");
  container.innerHTML = accounts.map(account => `
    <div class="account-item">
      <h4>${account.name}</h4>
      <p>${account.type} - ৳${account.balance}</p>
    </div>
  `).join("");
}


function showAddExpenseModal() {
  document.getElementById("addExpenseModal").style.display = "block";
}

function addExpense(category, amount) {
  expenses.push({ category, amount });
  updateUI();
}

function renderExpenseChart() {
  const ctx = document.getElementById("expenseChart").getContext("2d");
  const categories = [...new Set(expenses.map(e => e.category))];
  const data = categories.map(cat => expenses.filter(e => e.category === cat).reduce((sum, e) => sum + e.amount, 0));

  if (window.expenseChart) window.expenseChart.destroy();
  window.expenseChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: categories,
      datasets: [{
        data: data,
        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#8e44ad', '#27ae60'],
      }]
    },
    options: { responsive: true, maintainAspectRatio: false }
  });
}


function showAddTransactionModal() {
  document.getElementById("addTransactionModal").style.display = "block";
}

function addTransaction(desc, amount, category, date) {
  transactions.push({ desc, amount, category, date });
  updateUI();
}

function renderTransactions() {
  const list = document.getElementById("transactionsList");
  list.innerHTML = transactions.map(tx => `
    <div class="transaction-item">
      <strong>${tx.desc}</strong><br>
      <span>${tx.category} - ৳${tx.amount} (${tx.date})</span>
    </div>
  `).join("");
}


function showSetIncomeModal() {
  document.getElementById("setIncomeModal").style.display = "block";
}

function setIncome(amount) {
  income = amount;
  updateUI();
}


function hideQuickStart() {
  document.getElementById("quickStart").style.display = "none";
}


function closeModal(modalId) {
  document.getElementById(modalId).style.display = "none";
}


document.getElementById("addAccountForm").addEventListener("submit", function (e) {
  e.preventDefault();
  const name = document.getElementById("accountName").value;
  const type = document.getElementById("accountType").value;
  const balance = parseFloat(document.getElementById("accountBalance").value);
  addAccount(name, type, balance);
  closeModal("addAccountModal");
});

document.getElementById("addExpenseForm").addEventListener("submit", function (e) {
  e.preventDefault();
  const category = document.getElementById("expenseCategory").value;
  const amount = parseFloat(document.getElementById("expenseAmount").value);
  addExpense(category, amount);
  closeModal("addExpenseModal");
});

document.getElementById("addTransactionForm").addEventListener("submit", function (e) {
  e.preventDefault();
  const desc = document.getElementById("transactionDesc").value;
  const amount = parseFloat(document.getElementById("transactionAmount").value);
  const category = document.getElementById("transactionCategory").value;
  const date = document.getElementById("transactionDate").value;
  addTransaction(desc, amount, category, date);
  closeModal("addTransactionModal");
});

document.getElementById("setIncomeForm").addEventListener("submit", function (e) {
  e.preventDefault();
  const amount = parseFloat(document.getElementById("monthlyIncomeAmount").value);
  setIncome(amount);
  closeModal("setIncomeModal");
  hideQuickStart();
});

document.getElementById("toggleBalance").addEventListener("click", function () {
  const totalBalance = document.getElementById("totalBalance");
  const icon = document.getElementById("eyeIcon");
  if (totalBalance.textContent.includes("*") || totalBalance.textContent === "৳****") {
    updateUI();
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye");
  } else {
    totalBalance.textContent = "৳****";
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  }
});


updateUI();
