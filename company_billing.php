<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Company Billing - FactoryTrack</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .table-responsive { margin-top: 1rem; }
        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .select-all-container { display: flex; align-items: center; gap: 8px; font-weight: bold; }
        input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <header>
            <h1 id="pageTitle"><i class="fa-solid fa-file-invoice"></i> Generate Bill</h1>
        </header>

        <div class="card">
            <div class="action-bar">
                <div class="select-all-container">
                    <input type="checkbox" id="selectAll" checked>
                    <label for="selectAll">Select All Items</label>
                </div>
                <div>
                    <label style="margin-right: 10px; font-weight: bold;">
                        <input type="checkbox" id="consolidateBill" checked> Consolidate into single bill
                    </label>
                    <button id="printBtn" class="btn btn-primary"><i class="fa-solid fa-print"></i> Print Selected Items</button>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">Select</th>
                        <th>Date</th>
                        <th>Job Name</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount (₹)</th>
                    </tr>
                </thead>
                <tbody id="itemsTable">
                    <!-- Loaded via JS -->
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" style="text-align: right; font-size: 1.2rem;">Total Selected:</th>
                        <th id="totalSelectedAmount" style="font-size: 1.2rem; color: var(--primary);">₹0.00</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const customer = urlParams.get('customer');
        let worksData = [];

        document.addEventListener('DOMContentLoaded', () => {
            if (customer) {
                document.getElementById('pageTitle').innerHTML = `<i class="fa-solid fa-file-invoice"></i> Bill for ${customer}`;
                loadWorks();
            } else {
                document.getElementById('itemsTable').innerHTML = '<tr><td colspan="6" style="text-align:center;">No customer selected.</td></tr>';
            }
        });

        async function loadWorks() {
            try {
                const res = await fetch(`api.php?action=get_company_works&customer=${encodeURIComponent(customer)}`);
                worksData = await res.json();
                renderTable();
            } catch (e) {
                console.error("Failed to load works", e);
            }
        }

        function renderTable() {
            const tbody = document.getElementById('itemsTable');
            tbody.innerHTML = '';
            
            if (worksData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No pending works for this customer.</td></tr>';
                updateTotal();
                return;
            }

            worksData.forEach(work => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><input type="checkbox" class="item-checkbox" value="${work.id}" checked></td>
                    <td>${work.work_date}</td>
                    <td><strong>${work.job_name || '-'}</strong></td>
                    <td>${work.description}</td>
                    <td><span class="badge category-${(work.category || 'general').toLowerCase()}">${work.category || 'General'}</span></td>
                    <td>₹${parseFloat(work.amount).toFixed(2)}</td>
                `;
                tbody.appendChild(tr);
            });

            // Add event listeners to new checkboxes
            document.querySelectorAll('.item-checkbox').forEach(cb => {
                cb.addEventListener('change', () => {
                    updateTotal();
                    updateSelectAllState();
                });
            });

            updateTotal();
        }

        document.getElementById('selectAll').addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            document.querySelectorAll('.item-checkbox').forEach(cb => {
                cb.checked = isChecked;
            });
            updateTotal();
        });

        function updateSelectAllState() {
            const allChecked = Array.from(document.querySelectorAll('.item-checkbox')).every(cb => cb.checked);
            const someChecked = Array.from(document.querySelectorAll('.item-checkbox')).some(cb => cb.checked);
            const selectAllCb = document.getElementById('selectAll');
            
            selectAllCb.checked = allChecked;
            selectAllCb.indeterminate = someChecked && !allChecked;
        }

        function updateTotal() {
            let total = 0;
            const checkedIds = getSelectedIds();
            
            worksData.forEach(work => {
                if (checkedIds.includes(work.id.toString())) {
                    total += parseFloat(work.amount);
                }
            });
            
            document.getElementById('totalSelectedAmount').innerText = '₹' + total.toFixed(2);
        }

        function getSelectedIds() {
            return Array.from(document.querySelectorAll('.item-checkbox:checked')).map(cb => cb.value);
        }

        document.getElementById('printBtn').addEventListener('click', () => {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) {
                alert('Please select at least one item to print.');
                return;
            }
            
            const isConsolidated = document.getElementById('consolidateBill').checked;
            const idsParam = selectedIds.join(',');
            const url = `invoice.php?customer=${encodeURIComponent(customer)}&ids=${idsParam}&consolidated=${isConsolidated ? 1 : 0}`;
            
            window.open(url, '_blank');
        });
    </script>
</body>
</html>
