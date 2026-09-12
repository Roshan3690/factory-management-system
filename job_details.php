<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Details - FactoryTrack</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <header style="display: flex; align-items: center; justify-content: space-between;">
            <h1 id="pageTitle" style="margin: 0;">Edit Job</h1>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="cursor: pointer; display: flex; align-items: center; gap: 5px; font-size: 0.9rem;">
                    <input type="checkbox" id="includeOthers" checked> Include Others
                </label>
                <button onclick="generateInvoice()" class="btn btn-secondary">Print Invoice</button>
            </div>
        </header>

        <div class="card list-card" style="margin-bottom: 2rem;">
            <h2>Work Items</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount (₹)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="workTable">
                        <!-- Items -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inline Add Form for this job -->
         <div class="card form-card">
            <h2>Add New Cost to this Job</h2>
            <form id="addJobWorkForm">
                <input type="hidden" name="job_name" id="hiddenJobName">
                <!-- Customer name hidden? We might need to fetch it or just rely on backend associating it if we send it. 
                     For simplicity, we'll let the user re-enter or we fetch the existing one. 
                     Actually, better to fetch one item and get customer name. -->
                
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" id="categorySelect" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 0.375rem;">
                        <option value="VMC">VMC</option>
                        <option value="CNC">CNC</option>
                        <option value="Drill">Drill</option>
                        <option value="Lathe">Lathe</option>
                        <option value="Inspection">Inspection</option>
                        <option value="Fitting">Fitting</option>
                        <option value="Martial">Martial (Material)</option>
                        <option value="Parts">Parts</option>
                        <option value="Grinding">Grinding</option>
                        <option value="Labor">Labor</option>
                        <option value="Other">Other</option>
                    </select>
                    <input type="text" id="customCategoryInput" placeholder="Enter custom category" style="display: none; margin-top: 0.5rem; width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 0.375rem;">
                     
                    <!-- Drill Options -->
                    <div id="drillOptions" style="display: none; margin-top: 0.5rem; padding: 0.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px;">
                        <div style="display: flex; gap: 10px; margin-bottom: 5px;">
                            <div style="flex: 1;">
                                <label style="font-size: 0.8rem;">Type</label>
                                <select id="drillType" style="width: 100%; padding: 4px;">
                                    <option value="10">Hole (₹10)</option>
                                    <option value="20">Rimer (₹20)</option>
                                    <option value="50">Hole Thread (₹50)</option>
                                    <option value="90">Miling (₹90)</option>
                                </select>
                            </div>
                            <div style="flex: 1;">
                                    <label style="font-size: 0.8rem;">Count</label>
                                    <input type="number" id="drillCount" value="1" min="1" style="width: 100%; padding: 4px;">
                            </div>
                        </div>
                    </div>

                    <!-- Lathe Options -->
                    <div id="latheOptions" style="display: none; margin-top: 0.5rem; padding: 0.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px;">
                        <label style="font-size: 0.8rem;">Operation</label>
                        <select id="latheOp" style="width: 100%; padding: 4px;">
                            <option value="Precision turning">Precision Turning</option>
                            <option value="Normal turning">Normal Turning</option>
                            <option value="Thread">Thread</option>
                        </select>
                    </div>

                    <!-- Parts Options -->
                    <div id="partsOptions" style="display: none; margin-top: 0.5rem; padding: 0.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px;">
                        <div style="margin-bottom: 5px;">
                            <label style="font-size: 0.8rem;">Part Type</label>
                            <select id="partType" style="width: 100%; padding: 4px;">
                                <option value="Bolts">Bolts</option>
                                <option value="Pins">Pins</option>
                                <option value="Pin Buss">Pin Buss</option>
                            </select>
                        </div>
                        <div id="boltSizeDiv" style="margin-bottom: 5px;">
                            <label style="font-size: 0.8rem;">Size (Ani)</label>
                            <select id="boltSize" style="width: 100%; padding: 4px;">
                                <?php for($i=1; $i<=15; $i++) echo "<option value='$i'>$i Ani</option>"; ?>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 0.8rem;">Count</label>
                            <input type="number" id="partCount" value="1" min="1" style="width: 100%; padding: 4px;">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" placeholder="Description (Optional)">
                </div>
                <div class="form-group">
                    <label>Amount (₹)</label>
                    <input type="number" step="0.01" name="amount" required placeholder="0.00">
                </div>
                 <div class="form-group">
                    <label>Date</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="date" name="work_date" id="workDateInput" required>
                        <button type="button" class="btn btn-secondary btn-sm" id="setDateBtn">Today</button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Cost</button>
            </form>
         </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const jobName = urlParams.get('job_name');
        
        document.getElementById('hiddenJobName').value = jobName;
        document.getElementById('pageTitle').textContent = `Edit Job: ${jobName}`;
        // document.getElementById('invoiceLink') removed or replaced logic

        // Date Button
        document.getElementById('setDateBtn').addEventListener('click', () => {
            const date = new Date();
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            document.getElementById('workDateInput').value = `${year}-${month}-${day}`;
        });

         // Custom Category & Options logic
        const categorySelect = document.getElementById('categorySelect');
        const customCategoryInput = document.getElementById('customCategoryInput');
        
        const drillOptions = document.getElementById('drillOptions');
        const latheOptions = document.getElementById('latheOptions');
        const partsOptions = document.getElementById('partsOptions');

        const drillType = document.getElementById('drillType');
        const drillCount = document.getElementById('drillCount');
        const latheOp = document.getElementById('latheOp');
        const partType = document.getElementById('partType');
        const boltSizeDiv = document.getElementById('boltSizeDiv');
        const boltSize = document.getElementById('boltSize');
        const partCount = document.getElementById('partCount');

        const amountInput = document.querySelector('input[name="amount"]');
        const descInput = document.querySelector('input[name="description"]');

        function updateCalculations() {
            const cat = categorySelect.value;
            
            // Hide all first
            customCategoryInput.style.display = 'none';
            customCategoryInput.required = false;
            drillOptions.style.display = 'none';
            latheOptions.style.display = 'none';
            partsOptions.style.display = 'none';

            if (cat === 'Other') {
                customCategoryInput.style.display = 'block';
                customCategoryInput.required = true;
            } else if (cat === 'Drill') {
                drillOptions.style.display = 'block';
                
                const price = parseFloat(drillType.value) || 0;
                const count = parseInt(drillCount.value) || 0;
                amountInput.value = (price * count).toFixed(2);
                
                const typeText = drillType.options[drillType.selectedIndex].text.split(' (')[0];
                descInput.value = `Drill: ${typeText} x${count}`;

            } else if (cat === 'Lathe') {
                latheOptions.style.display = 'block';
                descInput.value = `Lathe: ${latheOp.value}`;
                
            } else if (cat === 'Parts') {
                partsOptions.style.display = 'block';
                const type = partType.value;
                const count = partCount.value;
                
                if (type === 'Bolts') {
                    boltSizeDiv.style.display = 'block';
                    const size = boltSize.value;
                    descInput.value = `Parts: Bolts (${size} Ani) x${count}`;
                } else {
                    boltSizeDiv.style.display = 'none';
                    descInput.value = `Parts: ${type} x${count}`;
                }
            }
        }

        categorySelect.addEventListener('change', updateCalculations);
        drillType.addEventListener('change', updateCalculations);
        drillCount.addEventListener('input', updateCalculations);
        latheOp.addEventListener('change', updateCalculations);
        partType.addEventListener('change', updateCalculations);
        boltSize.addEventListener('change', updateCalculations);
        partCount.addEventListener('input', updateCalculations);

        async function loadData() {
            const res = await fetch(`api.php?action=get_job_details&job_name=${encodeURIComponent(jobName)}`);
            const works = await res.json();
            const tbody = document.getElementById('workTable');
            tbody.innerHTML = '';
            
            works.forEach(work => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><input type="checkbox" class="work-checkbox" value="${work.id}" checked></td>
                    <td>${work.work_date}</td>
                    <td><input type="text" value="${work.category}" class="form-input-sm" onchange="updateItem(${work.id}, 'category', this.value)"></td>
                    <td><input type="text" value="${work.description}" class="form-input-sm" style="width:100%" onchange="updateItem(${work.id}, 'description', this.value)"></td>
                    <td>₹<input type="number" step="0.01" value="${work.amount}" class="form-input-sm" style="width:80px" onchange="updateItem(${work.id}, 'amount', this.value)"></td>
                    <td style="display: flex; gap: 10px; align-items: center;">
                         <span class="badge ${work.payment_status === 'Paid' ? 'badge-paid' : 'badge-pending'}">${work.payment_status}</span>
                         ${work.payment_status === 'Pending' 
                            ? `<button class="btn btn-sm btn-primary" onclick="markPaid(${work.id})"><i class="fa-solid fa-check"></i> Mark Paid</button>` 
                            : '<span style="color: green; font-weight: bold;"><i class="fa-solid fa-check-double"></i> Paid</span>'}
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
        
        async function markPaid(id) {
            if (!confirm('Mark this item as Paid?')) return;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', 'Paid');

            try {
                const res = await fetch('api.php?action=update_payment', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    loadData();
                } else {
                    alert('Failed to update status');
                }
            } catch (error) {
                alert('Error updating status');
            }
        }
        
        function generateInvoice() {
            const checkedBoxes = document.querySelectorAll('.work-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert("Please select at least one item.");
                return;
            }
            const ids = Array.from(checkedBoxes).map(cb => cb.value).join(',');
            const includeOthers = document.getElementById('includeOthers').checked ? 1 : 0;
            window.open(`invoice.php?job_name=${encodeURIComponent(jobName)}&ids=${ids}&include_others=${includeOthers}`, '_blank');
        }

        // Select All Flow
        document.getElementById('selectAll').addEventListener('change', (e) => {
            document.querySelectorAll('.work-checkbox').forEach(cb => cb.checked = e.target.checked);
        });

        async function updateItem(id, field, value) {
            // We need to send full object or just partial. API expects full 'description', 'amount', 'category'.
            // Simple way: Fetch the row data? Or just send what changed? 
            // My API simplistic implementation expects all fields. 
            // Better approach for this user: Let's just assume we send what we have in the input.
            // Since onchange only gives us one value, we have to find the row. 
            // Actually, let's just implement a robust update in API or a specialized JS function collecting all row inputs.
            // For now, to keep it simple and working:
            
            // Re-strategy: The user just wants to edit.
            // Let's grab the inputs from the current row.
            // This requires traversing DOM.
            // Let's simplify. I will make a 'Save' button approach or auto-save if I can easily get row data.
            // Let's implement a specific API call that updates just one field? No, standard update is safer.
            
            // To make "onchange" work effectively without full row parsing, I will update the API to allow partial updates 
            // OR I will parse the row. Let's parse the row since we are here.
            // BUT, wait, I can just reload the data into variables.
            
            // Let's prompt user for now to make sure they want to change it, 
            // but actually, providing a "Edit" modal might be cleaner. 
            // Given the constraints and speed, I will make the inputs read-only until a "Edit" button is clicked? 
            // No, inline inputs are requested.
            
            // Implementation: gathering siblings is messy. 
            // I'll assume for this iteration that "update_work_details" in API handles the full update.
            // Let's do a trick: get the row.
            const row = event.target.closest('tr');
            const cat = row.querySelector('input[onchange*="category"]').value;
            const desc = row.querySelector('input[onchange*="description"]').value;
            const amt = row.querySelector('input[onchange*="amount"]').value;
            
            const formData = new FormData();
            formData.append('action', 'update_work_details');
            formData.append('id', id);
            formData.append('description', desc);
            formData.append('amount', amt);
            formData.append('category', cat);
            
            await fetch('api.php', { method: 'POST', body: formData });
            // Optional: visual feedback
        }

        // Add Form
        document.getElementById('addJobWorkForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'add_work');
             // We need to ensure customer_name is consistent. 
             // We can fetch it from the first work item or just leave it empty relying on Job Name grouping.
             // Ideally we should pass it. 
             // Let's rely on the user having previously set it, or API fetching it.
             // Actually, my 'add_work' API inserts a new row. If I don't send customer_name, it will be blank.
             // That breaks the grouping for "Companies".
             // Quick fix: fetch customer name from the first item in the list and append it.
             
             // ... grabbing customer from table ...
             // Let's just fetch it first.
             const resCheck = await fetch(`api.php?action=get_job_details&job_name=${encodeURIComponent(jobName)}`);
             const dataCheck = await resCheck.json();
             if(dataCheck.length > 0) {
                 formData.append('customer_name', dataCheck[0].customer_name);
             }

             if (categorySelect.value === 'Other') {
                formData.set('category', customCategoryInput.value);
            }

            const res = await fetch('api.php', { method: 'POST', body: formData });
            const data = await res.json();
            if(data.status === 'success') {
                e.target.reset();
                loadData();
            }
        });

        loadData();
    </script>
    <style>
        .form-input-sm {
            padding: 4px; border: 1px solid #ddd; border-radius: 4px;
        }
    </style>
</body>
</html>
