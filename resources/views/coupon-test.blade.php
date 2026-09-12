<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CouponLab — Manual Testing Suite for Apply Coupon API</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="padding-bottom: 3rem;">

    <!-- Top Navigation Bar -->
    <header style="background: var(--bg-surface); border-bottom: 1px solid var(--border-card); position: sticky; top: 0; z-index: 100; backdrop-filter: blur(12px);">
        <div style="max-width: 1440px; margin: 0 auto; padding: 0.85rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.85rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 0 15px rgba(99, 102, 241, 0.5);">
                    🎟️
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-weight: 800; font-size: 1.15rem; letter-spacing: -0.02em; background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">CouponLab</span>
                        <span class="badge badge-indigo">API Workbench</span>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono);">POST /api/coupons/apply</div>
                </div>
            </div>

            <!-- Quick Controls -->
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <button id="reset-db-btn" class="btn-danger-subtle" title="Clear redemptions & restore initial test coupons">
                    <span>↺</span> Reset Test Data
                </button>
                <button id="theme-toggle-btn" class="btn-secondary" onclick="window.toggleTheme()" title="Toggle Dark/Light theme" style="padding: 0.45rem 0.75rem;">
                    ☀️
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main style="max-width: 1440px; margin: 1.5rem auto; padding: 0 1.5rem;">

        <!-- Preset Test Scenarios Carousel -->
        <section class="glass-card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 1.1rem;">⚡</span>
                    <h2 style="font-size: 0.95rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">
                        1-Click Test Scenarios (Business Rules Matrix)
                    </h2>
                </div>
                <span style="font-size: 0.75rem; color: var(--text-muted);">Click any card to auto-populate & test</span>
            </div>
            <div id="scenarios-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 0.65rem; max-height: 240px; overflow-y: auto; padding-right: 0.25rem;">
                <!-- Dynamically populated by JS -->
            </div>
        </section>

        <!-- Two Column Main Workspace -->
        <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 1.5rem; align-items: start;">

            <!-- LEFT COLUMN: Storefront Simulator & Checkout Tester -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">

                <!-- Cart & Customer Context Card -->
                <div class="glass-card" style="padding: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.15rem;">🛒</span>
                            <h2 style="font-size: 1.05rem; font-weight: 700;">Shopping Cart Simulator</h2>
                        </div>
                        <span class="badge badge-neutral">Client Request Builder</span>
                    </div>

                    <!-- Customer Context & Order Reference -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem;">
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.35rem; text-transform: uppercase; letter-spacing: 0.03em;">
                                Customer Context (user_id)
                            </label>
                            <select id="user-id-select" class="form-input">
                                <option value="guest">Guest Checkout (null)</option>
                                <option value="1">User #1 (Test User - used ONEPERUSER)</option>
                                <option value="2">User #2 (Fresh User)</option>
                                <option value="3">User #3 (Fresh User)</option>
                                <option value="99">User #99 (Custom)</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.35rem; text-transform: uppercase; letter-spacing: 0.03em;">
                                Order Reference (order_reference)
                            </label>
                            <div style="display: flex; gap: 0.35rem;">
                                <input id="order-reference-input" type="text" class="form-input code-mono" style="font-size: 0.85rem;" placeholder="ORD-12345" />
                                <button type="button" class="btn-secondary" onclick="window.generateNewOrderRef()" title="Generate new order ref" style="padding: 0.5rem 0.75rem;">
                                    🎲
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Products List -->
                    <div style="margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.03em;">
                                Products in Cart
                            </label>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">Adjust quantities to test amounts</span>
                        </div>
                        <div id="products-list" style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <!-- Populated by JS -->
                        </div>
                    </div>

                    <!-- Quick Bump Amount Chips -->
                    <div style="margin-bottom: 1.25rem; background: var(--bg-surface-elevated); padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border-subtle);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">Or Direct Cart Total ($):</span>
                            <div style="display: flex; gap: 0.35rem;">
                                <button class="btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.7rem;" onclick="window.setCustomTotal(50)">$50</button>
                                <button class="btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.7rem;" onclick="window.setCustomTotal(150)">$150</button>
                                <button class="btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.7rem;" onclick="window.setCustomTotal(250)">$250</button>
                                <button class="btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.7rem;" onclick="window.setCustomTotal(550)">$550 (Min500)</button>
                            </div>
                        </div>
                        <input id="custom-cart-input" type="number" step="0.01" min="0" class="form-input code-mono" style="font-weight: 700; font-size: 1.1rem;" placeholder="0.00" />
                    </div>

                    <!-- Coupon Input Bar -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.35rem; text-transform: uppercase; letter-spacing: 0.03em;">
                            Enter Coupon Code (code)
                        </label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input id="coupon-code-input" type="text" class="form-input code-mono" style="font-weight: 700; font-size: 1.05rem; letter-spacing: 0.05em; text-transform: uppercase;" placeholder="e.g. SAVE10, TENOFF..." />
                            <button id="apply-coupon-btn" class="btn-primary" style="white-space: nowrap; min-width: 140px;">
                                Apply Coupon
                            </button>
                            <button type="button" class="btn-secondary" onclick="window.removeCoupon()" title="Clear coupon" style="padding: 0.65rem 0.85rem;">
                                ✕
                            </button>
                        </div>
                    </div>

                    <!-- Live Order Summary Box -->
                    <div class="glass-subcard" style="padding: 1.25rem; border-color: rgba(99, 102, 241, 0.2);">
                        <div style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 0.85rem;">
                            Order Price Breakdown
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.95rem; margin-bottom: 0.5rem; color: var(--text-secondary);">
                            <span>Cart Subtotal</span>
                            <span id="summary-subtotal" class="code-mono" style="font-weight: 600; color: var(--text-primary);">$328.00</span>
                        </div>
                        <div id="summary-discount-row" style="display: none; justify-content: space-between; font-size: 0.95rem; margin-bottom: 0.5rem; color: var(--success);">
                            <span style="display: flex; align-items: center; gap: 0.35rem;">
                                <span>Discount Applied</span>
                                <span class="badge badge-success" style="font-size: 0.65rem; padding: 0.15rem 0.4rem;">PROMO</span>
                            </span>
                            <span id="summary-discount" class="code-mono" style="font-weight: 700;">-$0.00</span>
                        </div>
                        <div style="border-top: 1px dashed var(--border-subtle); margin: 0.75rem 0; padding-top: 0.75rem; display: flex; justify-content: space-between; align-items: baseline;">
                            <span style="font-weight: 700; font-size: 1.1rem;">Final Payable Amount</span>
                            <span id="summary-total" class="code-mono" style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary);">$328.00</span>
                        </div>
                        <div id="summary-savings-notice" style="display: none; margin-top: 0.65rem; font-size: 0.8rem; color: var(--success); font-weight: 600; text-align: right;">
                            <!-- Populated on success -->
                        </div>
                    </div>

                </div>

            </div>

            <!-- RIGHT COLUMN: Real-Time Network Inspector, Catalog & Audit Log -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">

                <!-- Real-Time Network & API Inspector -->
                <div class="glass-card" style="padding: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.15rem;">🔍</span>
                            <h2 style="font-size: 1.05rem; font-weight: 700;">API Request & Response Inspector</h2>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span id="inspector-latency" class="badge badge-neutral code-mono">0 ms</span>
                            <span id="inspector-status-badge" class="badge badge-neutral">Ready to Send</span>
                        </div>
                    </div>

                    <!-- Inspector Tab Switcher -->
                    <div style="display: flex; gap: 0.5rem; border-bottom: 1px solid var(--border-subtle); margin-bottom: 1rem;">
                        <button id="tab-btn-formatted" class="tab-btn active" onclick="window.switchInspectorTab('formatted')">
                            📊 Visual Result
                        </button>
                        <button id="tab-btn-response" class="tab-btn" onclick="window.switchInspectorTab('response')">
                            📥 Raw Response JSON
                        </button>
                        <button id="tab-btn-request" class="tab-btn" onclick="window.switchInspectorTab('request')">
                            📤 Raw Request JSON
                        </button>
                    </div>

                    <!-- Tab 1: Formatted Result -->
                    <div id="tab-content-formatted">
                        <div id="inspector-formatted-view">
                            <div style="padding: 2.5rem 1rem; text-align: center; color: var(--text-muted);">
                                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📡</div>
                                <div style="font-weight: 600; font-size: 0.95rem;">No Request Dispatched Yet</div>
                                <div style="font-size: 0.8rem; margin-top: 0.25rem;">Click "Apply Coupon" or choose a scenario above to test the API.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Raw Response JSON -->
                    <div id="tab-content-response" style="display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono);">HTTP Response Body:</span>
                            <button class="btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;" onclick="window.copyInspectorJSON('response')">
                                📋 Copy JSON
                            </button>
                        </div>
                        <pre id="inspector-raw-response" class="json-viewer">// Response JSON will appear here...</pre>
                    </div>

                    <!-- Tab 3: Raw Request JSON -->
                    <div id="tab-content-request" style="display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono);">HTTP Request Payload:</span>
                            <button class="btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;" onclick="window.copyInspectorJSON('request')">
                                📋 Copy JSON
                            </button>
                        </div>
                        <pre id="inspector-raw-request" class="json-viewer">// Request JSON will appear here...</pre>
                    </div>
                </div>

                <!-- Database Coupons Cheatsheet -->
                <div class="glass-card" style="padding: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.15rem;">📋</span>
                            <h2 style="font-size: 1.05rem; font-weight: 700;">Database Coupons Catalog</h2>
                        </div>
                        <span class="badge badge-indigo">Live Database State</span>
                    </div>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border-subtle); color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">
                                    <th style="padding: 0.5rem 0.75rem;">Code</th>
                                    <th style="padding: 0.5rem 0.75rem;">Type</th>
                                    <th style="padding: 0.5rem 0.75rem;">Value</th>
                                    <th style="padding: 0.5rem 0.75rem;">Min Order</th>
                                    <th style="padding: 0.5rem 0.75rem;">Uses / Limit</th>
                                    <th style="padding: 0.5rem 0.75rem;">Status</th>
                                    <th style="padding: 0.5rem 0.75rem; text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="coupons-catalog-table">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Database Redemptions Audit Log -->
                <div class="glass-card" style="padding: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.15rem;">📜</span>
                            <h2 style="font-size: 1.05rem; font-weight: 700;">Redemption Audit Trail</h2>
                        </div>
                        <span class="badge badge-neutral">coupon_redemptions table</span>
                    </div>

                    <div style="overflow-x: auto; max-height: 280px; overflow-y: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border-subtle); color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">
                                    <th style="padding: 0.5rem 0.75rem;">ID</th>
                                    <th style="padding: 0.5rem 0.75rem;">Coupon</th>
                                    <th style="padding: 0.5rem 0.75rem;">User</th>
                                    <th style="padding: 0.5rem 0.75rem;">Order Ref</th>
                                    <th style="padding: 0.5rem 0.75rem;">Discount</th>
                                    <th style="padding: 0.5rem 0.75rem;">Redeemed</th>
                                </tr>
                            </thead>
                            <tbody id="redemptions-table-body">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <!-- Toast Notification Container -->
    <div id="toast-container"></div>

    <!-- Script for Inspector Tabs -->
    <script>
        window.switchInspectorTab = function(tab) {
            document.getElementById('tab-btn-formatted').className = 'tab-btn' + (tab === 'formatted' ? ' active' : '');
            document.getElementById('tab-btn-response').className = 'tab-btn' + (tab === 'response' ? ' active' : '');
            document.getElementById('tab-btn-request').className = 'tab-btn' + (tab === 'request' ? ' active' : '');

            document.getElementById('tab-content-formatted').style.display = tab === 'formatted' ? 'block' : 'none';
            document.getElementById('tab-content-response').style.display = tab === 'response' ? 'block' : 'none';
            document.getElementById('tab-content-request').style.display = tab === 'request' ? 'block' : 'none';
        };
    </script>
</body>
</html>
