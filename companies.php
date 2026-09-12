<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Companies - FactoryTrack</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .modal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;
        }
        .modal-content {
            background: #fff; padding: 2rem; border-radius: 12px; width: 90%; max-width: 500px;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <header>
            <h1><i class="fa-solid fa-building"></i> Customer List</h1>
        </header>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th style="font-size: 1.1rem;">Customer Name</th>
                        <th style="font-size: 1.1rem;">Address / GSTIN</th>
                        <th style="font-size: 1.1rem;">Total Spent (₹)</th>
                        <th style="font-size: 1.1rem;">Actions</th>
                    </tr>
                </thead>
                <tbody id="companiesTable">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div id="customerModal" class="modal">
        <div class="modal-content">
            <h2><i class="fa-solid fa-pen-to-square"></i> Edit Customer Details</h2>
            <p style="margin-bottom: 1rem; color: #666;">These details will appear on their invoices.</p>
            <form id="customerForm">
                <input type="hidden" id="custName">
                <div class="form-group">
                    <label style="font-size: 1.1rem;">Address</label>
                    <textarea id="custAddress" rows="3" style="width: 100%; padding: 0.8rem; font-size: 1.1rem; border-radius: 8px; border: 2px solid #cbd5e1;"></textarea>
                </div>
                <div class="form-group">
                    <label style="font-size: 1.1rem;">GSTIN Number</label>
                    <input type="text" id="custGstin" style="padding: 0.8rem; font-size: 1.1rem; border-radius: 8px; border: 2px solid #cbd5e1;">
                </div>
                <div class="form-group">
                    <label style="font-size: 1.1rem;">Phone Number</label>
                    <input type="text" id="custPhone" style="padding: 0.8rem; font-size: 1.1rem; border-radius: 8px; border: 2px solid #cbd5e1;">
                </div>
                <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;"><i class="fa-solid fa-save"></i> Save Details</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()" style="flex: 1;"><i class="fa-solid fa-times"></i> Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        async function loadCompanies() {
            const res = await fetch('api.php?action=get_companies');
            const companies = await res.json();
            const tbody = document.getElementById('companiesTable');
            tbody.innerHTML = '';
            
            companies.forEach(company => {
                const addressStr = company.address ? company.address + (company.gstin ? ' | GST: ' + company.gstin : '') : '<i style="color: #999;">No details added</i>';
                
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-size: 1.2rem;"><strong>${company.customer_name}</strong></td>
                    <td style="font-size: 1rem; color: #555;">${addressStr}</td>
                    <td style="font-size: 1.2rem; font-weight: bold;">₹${parseFloat(company.total_spent).toFixed(2)}</td>
                    <td>
                        <button onclick="openModal('${company.customer_name}', '${company.address || ''}', '${company.gstin || ''}', '${company.phone || ''}')" class="btn btn-secondary" style="margin-right: 5px;"><i class="fa-solid fa-edit"></i> Edit</button>
                        <a href="jobs.php?customer=${encodeURIComponent(company.customer_name)}" class="btn btn-primary"><i class="fa-solid fa-folder-open"></i> View Jobs</a>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function openModal(name, address, gstin, phone) {
            document.getElementById('custName').value = name;
            document.getElementById('custAddress').value = address;
            document.getElementById('custGstin').value = gstin;
            document.getElementById('custPhone').value = phone;
            document.getElementById('customerModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('customerModal').style.display = 'none';
        }

        document.getElementById('customerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData();
            fd.append('action', 'update_customer');
            fd.append('name', document.getElementById('custName').value);
            fd.append('address', document.getElementById('custAddress').value);
            fd.append('gstin', document.getElementById('custGstin').value);
            fd.append('phone', document.getElementById('custPhone').value);

            await fetch('api.php', { method: 'POST', body: fd });
            closeModal();
            loadCompanies();
        });

        document.addEventListener('DOMContentLoaded', loadCompanies);
    </script>
</body>
</html>
