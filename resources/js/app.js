import './bootstrap';

// Application State
const state = {
  products: [
    { id: 1, name: 'Pro Studio Wireless Headphones', category: 'Audio', price: 199.00, qty: 1, icon: '🎧' },
    { id: 2, name: 'Custom Mechanical RGB Keyboard', category: 'Peripheral', price: 129.00, qty: 1, icon: '⌨️' },
    { id: 3, name: 'Smart Fitness & Health Watch', category: 'Wearable', price: 89.00, qty: 0, icon: '⌚' },
    { id: 4, name: 'Precision Ergonomic Gaming Mouse', category: 'Peripheral', price: 59.00, qty: 0, icon: '🖱️' },
    { id: 5, name: '100W GaN Fast Charger & Cable', category: 'Power', price: 25.00, qty: 0, icon: '⚡' },
  ],
  customTotalMode: false,
  customTotalValue: 100.00,
  selectedUserId: '1',
  orderReference: 'ORD-' + Math.floor(10000 + Math.random() * 90000),
  couponCode: 'SAVE10',
  isApplying: false,
  lastRequest: null,
  lastResponse: null,
  appliedResult: null,
  couponsList: [],
  redemptionsList: [],
};

// Preset Scenarios
const scenarios = [
  {
    id: 'save10',
    title: 'SAVE10 (Fixed $10 Off)',
    badge: 'Valid',
    badgeClass: 'badge-success',
    desc: 'Fixed $10 discount on any valid order',
    code: 'SAVE10',
    cartTotal: 100.00,
    userId: '1',
  },
  {
    id: 'tenoff',
    title: 'TENOFF (10% Off)',
    badge: 'Valid',
    badgeClass: 'badge-success',
    desc: '10% percentage discount, no cap',
    code: 'TENOFF',
    cartTotal: 150.00,
    userId: '1',
  },
  {
    id: 'halfoff',
    title: 'HALFOFF (50% Capped at $100)',
    badge: 'Cap Rule',
    badgeClass: 'badge-indigo',
    desc: '$300 cart = $150 discount capped at max $100',
    code: 'HALFOFF',
    cartTotal: 300.00,
    userId: '1',
  },
  {
    id: 'save50-pass',
    title: 'SAVE50 (Min $200 Met)',
    badge: 'Valid',
    badgeClass: 'badge-success',
    desc: '$50 off on orders of $200 or more',
    code: 'SAVE50',
    cartTotal: 250.00,
    userId: '1',
  },
  {
    id: 'min500-fail',
    title: 'MIN500 (Min Order Rejected)',
    badge: '422 Reject',
    badgeClass: 'badge-danger',
    desc: 'Cart $150 < Min Order $500 threshold',
    code: 'MIN500',
    cartTotal: 150.00,
    userId: '1',
  },
  {
    id: 'min500-pass',
    title: 'MIN500 (Min Order Satisfied)',
    badge: 'Valid',
    badgeClass: 'badge-success',
    desc: 'Cart $600 satisfies min order threshold',
    code: 'MIN500',
    cartTotal: 600.00,
    userId: '1',
  },
  {
    id: 'expired10',
    title: 'EXPIRED10 (Expired Date)',
    badge: '422 Reject',
    badgeClass: 'badge-danger',
    desc: 'Coupon expired 3 days ago',
    code: 'EXPIRED10',
    cartTotal: 100.00,
    userId: '1',
  },
  {
    id: 'comingsoon',
    title: 'COMINGSOON (Future Date)',
    badge: '422 Reject',
    badgeClass: 'badge-danger',
    desc: 'Starts in 7 days, not active yet',
    code: 'COMINGSOON',
    cartTotal: 100.00,
    userId: '1',
  },
  {
    id: 'disabled10',
    title: 'DISABLED10 (Inactive)',
    badge: '422 Reject',
    badgeClass: 'badge-danger',
    desc: 'Coupon is set to active = false',
    code: 'DISABLED10',
    cartTotal: 100.00,
    userId: '1',
  },
  {
    id: 'onlyone',
    title: 'ONLYONE (Usage Limit Reached)',
    badge: '422 Reject',
    badgeClass: 'badge-danger',
    desc: 'Global limit is 1, already redeemed in seeder',
    code: 'ONLYONE',
    cartTotal: 100.00,
    userId: '1',
  },
  {
    id: 'oneperuser-fail',
    title: 'ONEPERUSER (User 1 Limit)',
    badge: '422 Reject',
    badgeClass: 'badge-danger',
    desc: 'User #1 already used their 1 redemption',
    code: 'ONEPERUSER',
    cartTotal: 100.00,
    userId: '1',
  },
  {
    id: 'oneperuser-pass',
    title: 'ONEPERUSER (User 2 Allowed)',
    badge: 'Valid',
    badgeClass: 'badge-success',
    desc: 'User #2 has not used it yet -> succeeds!',
    code: 'ONEPERUSER',
    cartTotal: 100.00,
    userId: '2',
  },
  {
    id: 'fake-code',
    title: 'FAKECODE (Not Found)',
    badge: '422 Reject',
    badgeClass: 'badge-danger',
    desc: 'Tests lookup failure for non-existent coupon',
    code: 'NONEXISTENT99',
    cartTotal: 100.00,
    userId: '1',
  },
  {
    id: 'invalid-input',
    title: 'Invalid Input (Negative Cart)',
    badge: '422 Format',
    badgeClass: 'badge-danger',
    desc: 'cart_total: -10 triggers FormRequest validation',
    code: 'SAVE10',
    cartTotal: -10.00,
    userId: '1',
  },
];

// Helper: Calculate Cart Total
function getCartSubtotal() {
  if (state.customTotalMode) {
    return Math.max(0, Number(state.customTotalValue) || 0);
  }
  return state.products.reduce((acc, p) => acc + p.price * p.qty, 0);
}

// Helper: Format Currency
function formatMoney(amount) {
  return '$' + Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Show Toast
function showToast(message, type = 'info') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = 'toast';
  
  let icon = 'ℹ️';
  if (type === 'success') icon = '✅';
  if (type === 'error') icon = '❌';
  if (type === 'warning') icon = '⚠️';

  toast.innerHTML = `<span>${icon}</span><span>${message}</span>`;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(12px) scale(0.95)';
    setTimeout(() => toast.remove(), 200);
  }, 3500);
}

// Render Products in Cart
function renderProducts() {
  const container = document.getElementById('products-list');
  if (!container) return;

  container.innerHTML = state.products.map(p => `
    <div class="product-card">
      <div style="display: flex; align-items: center; gap: 0.75rem;">
        <span style="font-size: 1.5rem;">${p.icon}</span>
        <div>
          <div style="font-weight: 600; font-size: 0.875rem;">${p.name}</div>
          <div style="font-size: 0.75rem; color: var(--text-muted);">${formatMoney(p.price)} &bull; ${p.category}</div>
        </div>
      </div>
      <div style="display: flex; align-items: center; gap: 0.5rem;">
        <button class="qty-btn" onclick="window.updateQty(${p.id}, -1)">-</button>
        <span style="min-width: 24px; text-align: center; font-weight: 700; font-family: var(--font-mono); font-size: 0.9rem;">${p.qty}</span>
        <button class="qty-btn" onclick="window.updateQty(${p.id}, 1)">+</button>
      </div>
    </div>
  `).join('');
}

// Render Preset Scenarios
function renderScenarios() {
  const container = document.getElementById('scenarios-grid');
  if (!container) return;

  container.innerHTML = scenarios.map(s => `
    <div class="scenario-pill ${state.couponCode === s.code ? 'active' : ''}" onclick="window.selectScenario('${s.id}')">
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: 600; font-size: 0.825rem; font-family: var(--font-mono);">${s.code}</span>
        <span class="badge ${s.badgeClass}">${s.badge}</span>
      </div>
      <div style="font-size: 0.75rem; color: var(--text-secondary); line-height: 1.3;">${s.desc}</div>
      <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.2rem;">Cart: ${formatMoney(s.cartTotal)} &bull; User: ${s.userId || 'Guest'}</div>
    </div>
  `).join('');
}

// Render Order Summary
function updateOrderSummary() {
  const subtotal = getCartSubtotal();
  const subtotalEl = document.getElementById('summary-subtotal');
  const discountRow = document.getElementById('summary-discount-row');
  const discountEl = document.getElementById('summary-discount');
  const totalEl = document.getElementById('summary-total');
  const savingsNotice = document.getElementById('summary-savings-notice');
  const customTotalInput = document.getElementById('custom-cart-input');

  if (subtotalEl) subtotalEl.innerText = formatMoney(subtotal);
  if (customTotalInput) customTotalInput.value = subtotal.toFixed(2);

  if (state.appliedResult && state.appliedResult.valid) {
    if (discountRow) discountRow.style.display = 'flex';
    if (discountEl) discountEl.innerText = '-' + formatMoney(state.appliedResult.discount_amount);
    if (totalEl) totalEl.innerText = formatMoney(state.appliedResult.final_total);
    if (savingsNotice) {
      const pct = subtotal > 0 ? Math.round((state.appliedResult.discount_amount / subtotal) * 100) : 0;
      savingsNotice.style.display = 'block';
      savingsNotice.innerHTML = `🎉 You saved <strong>${formatMoney(state.appliedResult.discount_amount)}</strong> (${pct}% off)!`;
    }
  } else {
    if (discountRow) discountRow.style.display = 'none';
    if (totalEl) totalEl.innerText = formatMoney(subtotal);
    if (savingsNotice) savingsNotice.style.display = 'none';
  }
}

// Render Live Coupons Cheatsheet
function renderCouponsCatalog() {
  const container = document.getElementById('coupons-catalog-table');
  if (!container) return;

  if (!state.couponsList || state.couponsList.length === 0) {
    container.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 1.5rem; color: var(--text-muted);">Loading coupons...</td></tr>`;
    return;
  }

  container.innerHTML = state.couponsList.map(c => {
    let badgeClass = 'badge-success';
    if (c.status === 'expired' || c.status === 'disabled' || c.status === 'exhausted') badgeClass = 'badge-danger';
    if (c.status === 'scheduled') badgeClass = 'badge-warning';

    const usesText = c.usage_limit !== null ? `${c.redemptions_count} / ${c.usage_limit}` : `${c.redemptions_count} (No limit)`;
    const userLimitText = c.usage_limit_per_user !== null ? `${c.usage_limit_per_user} max/user` : 'Unlimited';

    return `
      <tr style="border-bottom: 1px solid var(--border-subtle); transition: background 0.15s ease;">
        <td style="padding: 0.65rem 0.75rem;">
          <span style="font-family: var(--font-mono); font-weight: 700; color: var(--accent-primary);">${c.code}</span>
        </td>
        <td style="padding: 0.65rem 0.75rem; text-transform: capitalize; font-size: 0.8rem;">
          ${c.type}
        </td>
        <td style="padding: 0.65rem 0.75rem; font-weight: 600; font-family: var(--font-mono);">
          ${c.formatted_value} ${c.max_discount_amount ? `<small style="color:var(--text-muted)">(max $${c.max_discount_amount})</small>` : ''}
        </td>
        <td style="padding: 0.65rem 0.75rem; font-size: 0.8rem; font-family: var(--font-mono);">
          ${c.min_order_value > 0 ? '$' + c.min_order_value.toFixed(2) : '$0.00'}
        </td>
        <td style="padding: 0.65rem 0.75rem; font-size: 0.8rem;">
          ${usesText} <br><small style="color:var(--text-muted)">${userLimitText}</small>
        </td>
        <td style="padding: 0.65rem 0.75rem;">
          <span class="badge ${badgeClass}">${c.status_badge}</span>
        </td>
        <td style="padding: 0.65rem 0.75rem; text-align: right;">
          <button class="btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;" onclick="window.loadCouponIntoTester('${c.code}', ${c.min_order_value})">
            Test &rarr;
          </button>
        </td>
      </tr>
    `;
  }).join('');
}

// Render Redemptions Audit Log
function renderRedemptions() {
  const container = document.getElementById('redemptions-table-body');
  if (!container) return;

  if (!state.redemptionsList || state.redemptionsList.length === 0) {
    container.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 1.5rem; color: var(--text-muted);">No redemptions recorded yet.</td></tr>`;
    return;
  }

  container.innerHTML = state.redemptionsList.map(r => `
    <tr style="border-bottom: 1px solid var(--border-subtle);">
      <td style="padding: 0.65rem 0.75rem; font-family: var(--font-mono); font-size: 0.8rem; color: var(--text-muted);">#${r.id}</td>
      <td style="padding: 0.65rem 0.75rem; font-family: var(--font-mono); font-weight: 600; color: var(--accent-primary);">${r.coupon_code}</td>
      <td style="padding: 0.65rem 0.75rem; font-size: 0.8rem;">${r.user_id !== null ? 'User #' + r.user_id : '<span style="color:var(--text-muted)">Guest</span>'}</td>
      <td style="padding: 0.65rem 0.75rem; font-family: var(--font-mono); font-size: 0.8rem; color: var(--text-secondary);">${r.order_reference}</td>
      <td style="padding: 0.65rem 0.75rem; font-family: var(--font-mono); font-weight: 700; color: var(--success);">${formatMoney(r.amount_discounted)}</td>
      <td style="padding: 0.65rem 0.75rem; font-size: 0.75rem; color: var(--text-muted);">${r.redeemed_at}</td>
    </tr>
  `).join('');
}

// Update Network Inspector Views
function updateInspector(status, latencyMs, reqData, resData) {
  const statusEl = document.getElementById('inspector-status-badge');
  const latencyEl = document.getElementById('inspector-latency');
  const formattedView = document.getElementById('inspector-formatted-view');
  const rawReqEl = document.getElementById('inspector-raw-request');
  const rawResEl = document.getElementById('inspector-raw-response');

  if (latencyEl) latencyEl.innerText = `${latencyMs} ms`;

  // Status Badge
  if (statusEl) {
    if (status === 200) {
      statusEl.className = 'badge badge-success';
      statusEl.innerHTML = `<span class="pulse-dot"></span> 200 OK — Valid Redemption`;
    } else if (status === 422) {
      statusEl.className = 'badge badge-danger';
      statusEl.innerHTML = `<span class="pulse-dot"></span> 422 Unprocessable Content`;
    } else {
      statusEl.className = 'badge badge-warning';
      statusEl.innerHTML = `<span class="pulse-dot"></span> HTTP ${status}`;
    }
  }

  // Raw Request
  if (rawReqEl) {
    const rawReq = {
      endpoint: 'POST /api/coupons/apply',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: reqData,
    };
    rawReqEl.innerText = JSON.stringify(rawReq, null, 2);
  }

  // Raw Response
  if (rawResEl) {
    rawResEl.innerText = JSON.stringify(resData, null, 2);
  }

  // Formatted View
  if (formattedView) {
    if (status === 200 && resData.valid) {
      formattedView.innerHTML = `
        <div style="background: var(--success-bg); border: 1px solid var(--success-border); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
          <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--success); font-size: 1rem;">
            <span>✓</span> Coupon "${resData.coupon_code}" Applied Successfully!
          </div>
          <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.35rem;">
            Redemption saved atomically to database.
          </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
          <div class="glass-subcard" style="padding: 0.75rem; text-align: center;">
            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Coupon Code</div>
            <div style="font-size: 1.1rem; font-weight: 700; font-family: var(--font-mono); color: var(--accent-primary);">${resData.coupon_code}</div>
          </div>
          <div class="glass-subcard" style="padding: 0.75rem; text-align: center;">
            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Discount Amount</div>
            <div style="font-size: 1.1rem; font-weight: 700; font-family: var(--font-mono); color: var(--success);">${formatMoney(resData.discount_amount)}</div>
          </div>
          <div class="glass-subcard" style="padding: 0.75rem; text-align: center;">
            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Final Total</div>
            <div style="font-size: 1.1rem; font-weight: 700; font-family: var(--font-mono);">${formatMoney(resData.final_total)}</div>
          </div>
        </div>
      `;
    } else {
      const reasonText = resData.reason || resData.message || (resData.errors ? Object.values(resData.errors).flat().join(', ') : 'Coupon rejected');
      formattedView.innerHTML = `
        <div style="background: var(--danger-bg); border: 1px solid var(--danger-border); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
          <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--danger); font-size: 1rem;">
            <span>✕</span> Coupon Application Rejected
          </div>
          <div style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500; margin-top: 0.4rem;">
            ${reasonText}
          </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
          <div class="glass-subcard" style="padding: 0.75rem; text-align: center;">
            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Discount Applied</div>
            <div style="font-size: 1.1rem; font-weight: 700; font-family: var(--font-mono); color: var(--danger);">$0.00</div>
          </div>
          <div class="glass-subcard" style="padding: 0.75rem; text-align: center;">
            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Final Payable</div>
            <div style="font-size: 1.1rem; font-weight: 700; font-family: var(--font-mono);">${formatMoney(resData.final_total || getCartSubtotal())}</div>
          </div>
        </div>
      `;
    }
  }
}

// API Action: Apply Coupon
async function applyCoupon() {
  const codeInput = document.getElementById('coupon-code-input');
  const code = (codeInput ? codeInput.value : state.couponCode).trim();
  const cartTotal = getCartSubtotal();
  const userId = state.selectedUserId === 'guest' ? null : (state.selectedUserId ? parseInt(state.selectedUserId, 10) : null);
  const orderRef = state.orderReference;

  if (!code) {
    showToast('Please enter a coupon code.', 'warning');
    return;
  }

  state.isApplying = true;
  const applyBtn = document.getElementById('apply-coupon-btn');
  if (applyBtn) {
    applyBtn.disabled = true;
    applyBtn.innerHTML = `<span class="pulse-dot"></span> Applying...`;
  }

  const payload = {
    code: code,
    cart_total: cartTotal,
    user_id: userId,
    order_reference: orderRef,
  };

  state.lastRequest = payload;
  const startTime = performance.now();

  try {
    const response = await fetch('/api/coupons/apply', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload),
    });

    const latencyMs = Math.round(performance.now() - startTime);
    const data = await response.json();
    state.lastResponse = data;

    updateInspector(response.status, latencyMs, payload, data);

    if (response.ok && data.valid) {
      state.appliedResult = data;
      showToast(`Coupon ${data.coupon_code} applied! -$${Number(data.discount_amount).toFixed(2)}`, 'success');
    } else {
      state.appliedResult = null;
      const msg = data.reason || data.message || 'Coupon could not be applied';
      showToast(`Rejected: ${msg}`, 'error');
    }

    updateOrderSummary();

    // Auto refresh catalog and redemptions to reflect state
    fetchCoupons();
    fetchRedemptions();

  } catch (err) {
    const latencyMs = Math.round(performance.now() - startTime);
    showToast(`Network Error: ${err.message}`, 'error');
    updateInspector(500, latencyMs, payload, { error: err.message });
  } finally {
    state.isApplying = false;
    if (applyBtn) {
      applyBtn.disabled = false;
      applyBtn.innerHTML = `Apply Coupon`;
    }
  }
}

// Fetch Coupons Catalog
async function fetchCoupons() {
  try {
    const res = await fetch('/test-api/coupons');
    if (res.ok) {
      state.couponsList = await res.json();
      renderCouponsCatalog();
    }
  } catch (e) {
    console.error('Failed to load coupons:', e);
  }
}

// Fetch Redemptions
async function fetchRedemptions() {
  try {
    const res = await fetch('/test-api/redemptions');
    if (res.ok) {
      state.redemptionsList = await res.json();
      renderRedemptions();
    }
  } catch (e) {
    console.error('Failed to load redemptions:', e);
  }
}

// Reset Database
async function resetDatabase() {
  const btn = document.getElementById('reset-db-btn');
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = `<span class="pulse-dot"></span> Resetting...`;
  }

  try {
    const res = await fetch('/test-api/reset', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      }
    });

    const data = await res.json();
    showToast('Database reset: fresh coupons restored!', 'success');
    state.appliedResult = null;
    updateOrderSummary();
    await fetchCoupons();
    await fetchRedemptions();
  } catch (err) {
    showToast('Failed to reset database: ' + err.message, 'error');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = `<span>↺</span> Reset Test Data`;
    }
  }
}

// Window bindings for inline event handlers
window.updateQty = (productId, delta) => {
  const p = state.products.find(item => item.id === productId);
  if (p) {
    p.qty = Math.max(0, p.qty + delta);
    state.customTotalMode = false;
    renderProducts();
    updateOrderSummary();
  }
};

window.setCustomTotal = (amount) => {
  state.customTotalMode = true;
  state.customTotalValue = Number(amount);
  const input = document.getElementById('custom-cart-input');
  if (input) input.value = amount;
  updateOrderSummary();
};

window.selectScenario = (scenarioId) => {
  const s = scenarios.find(item => item.id === scenarioId);
  if (!s) return;

  state.couponCode = s.code;
  const codeInput = document.getElementById('coupon-code-input');
  if (codeInput) codeInput.value = s.code;

  state.selectedUserId = s.userId;
  const userSelect = document.getElementById('user-id-select');
  if (userSelect) userSelect.value = s.userId;

  window.setCustomTotal(s.cartTotal);
  renderScenarios();
  showToast(`Scenario loaded: ${s.title}`, 'info');

  // Auto apply
  applyCoupon();
};

window.loadCouponIntoTester = (code, minOrder) => {
  state.couponCode = code;
  const codeInput = document.getElementById('coupon-code-input');
  if (codeInput) codeInput.value = code;

  // If current cart total is less than min order, suggest bump
  if (minOrder > 0 && getCartSubtotal() < minOrder) {
    window.setCustomTotal(minOrder + 50);
  }

  showToast(`Coupon ${code} loaded into tester!`, 'info');
  applyCoupon();
};

window.removeCoupon = () => {
  state.appliedResult = null;
  state.couponCode = '';
  const codeInput = document.getElementById('coupon-code-input');
  if (codeInput) codeInput.value = '';
  updateOrderSummary();
  showToast('Coupon removed.', 'info');
};

window.generateNewOrderRef = () => {
  state.orderReference = 'ORD-' + Math.floor(10000 + Math.random() * 90000);
  const refInput = document.getElementById('order-reference-input');
  if (refInput) refInput.value = state.orderReference;
};

window.copyInspectorJSON = (tab) => {
  let text = '';
  if (tab === 'request') text = document.getElementById('inspector-raw-request')?.innerText || '';
  if (tab === 'response') text = document.getElementById('inspector-raw-response')?.innerText || '';

  if (text) {
    navigator.clipboard.writeText(text).then(() => {
      showToast('JSON copied to clipboard!', 'success');
    });
  }
};

window.toggleTheme = () => {
  const current = document.documentElement.getAttribute('data-theme') || 'dark';
  const next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  const themeBtn = document.getElementById('theme-toggle-btn');
  if (themeBtn) themeBtn.innerText = next === 'dark' ? '☀️' : '🌙';
};

// Initialization on DOM ready
document.addEventListener('DOMContentLoaded', () => {
  renderProducts();
  renderScenarios();
  updateOrderSummary();

  const codeInput = document.getElementById('coupon-code-input');
  if (codeInput) {
    codeInput.value = state.couponCode;
    codeInput.addEventListener('input', (e) => {
      state.couponCode = e.target.value.toUpperCase();
    });
    codeInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') applyCoupon();
    });
  }

  const userSelect = document.getElementById('user-id-select');
  if (userSelect) {
    userSelect.value = state.selectedUserId;
    userSelect.addEventListener('change', (e) => {
      state.selectedUserId = e.target.value;
    });
  }

  const orderRefInput = document.getElementById('order-reference-input');
  if (orderRefInput) {
    orderRefInput.value = state.orderReference;
    orderRefInput.addEventListener('input', (e) => {
      state.orderReference = e.target.value;
    });
  }

  const customCartInput = document.getElementById('custom-cart-input');
  if (customCartInput) {
    customCartInput.addEventListener('input', (e) => {
      state.customTotalMode = true;
      state.customTotalValue = parseFloat(e.target.value) || 0;
      updateOrderSummary();
    });
  }

  const applyBtn = document.getElementById('apply-coupon-btn');
  if (applyBtn) {
    applyBtn.addEventListener('click', applyCoupon);
  }

  const resetBtn = document.getElementById('reset-db-btn');
  if (resetBtn) {
    resetBtn.addEventListener('click', resetDatabase);
  }

  // Load initial data
  fetchCoupons();
  fetchRedemptions();
});
