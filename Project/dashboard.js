
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
  
  // Make the current page's nav link active
  const currentPath = window.location.pathname;
  const filename = currentPath.substring(currentPath.lastIndexOf('/') + 1);
  const navLinks = document.querySelectorAll('.nav-link li a');
  navLinks.forEach(link => {
    if (link.getAttribute('href') === filename) {
      link.classList.add('active');
    }
  });
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


function closeAndReset(modalId, formId) {
  closeModal(modalId);
  if (formId) {
    const f = document.getElementById(formId);
    if (f) f.reset();
  }
}



function safeNumber(val) {
  const n = parseFloat(val);
  return isFinite(n) ? n : 0;
}

document.getElementById("addAccountForm").addEventListener("submit", function (e) {
  e.preventDefault();
  const name = document.getElementById("accountName").value.trim();
  const type = document.getElementById("accountType").value;
  const balance = safeNumber(document.getElementById("accountBalance").value);
  if (!name || !type) return; // basic guard
  addAccount(name, type, balance);
  closeAndReset("addAccountModal", "addAccountForm");
});

document.getElementById("addExpenseForm").addEventListener("submit", function (e) {
  e.preventDefault();
  const category = document.getElementById("expenseCategory").value.trim();
  const amount = safeNumber(document.getElementById("expenseAmount").value);
  if (!category) return;
  addExpense(category, amount);
  closeAndReset("addExpenseModal", "addExpenseForm");
});

document.getElementById("addTransactionForm").addEventListener("submit", function (e) {
  e.preventDefault();
  const desc = document.getElementById("transactionDesc").value.trim();
  const amount = safeNumber(document.getElementById("transactionAmount").value);
  const category = document.getElementById("transactionCategory").value.trim();
  const date = document.getElementById("transactionDate").value;
  if (!desc || !category || !date) return;
  addTransaction(desc, amount, category, date);
  closeAndReset("addTransactionModal", "addTransactionForm");
});

document.getElementById("setIncomeForm").addEventListener("submit", function (e) {
  e.preventDefault();
  const amount = safeNumber(document.getElementById("monthlyIncomeAmount").value);
  setIncome(amount);
  closeAndReset("setIncomeModal", "setIncomeForm");
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

// Close on outside click
window.addEventListener('click', (e) => {
  if (e.target.classList && e.target.classList.contains('modal')) {
    e.target.style.display = 'none';
  }
});

// Escape key closes topmost open modal
window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    const openModals = Array.from(document.querySelectorAll('.modal'))
      .filter(m => m.style.display === 'block');
    if (openModals.length) {
      openModals[openModals.length - 1].style.display = 'none';
    }
  }
});
