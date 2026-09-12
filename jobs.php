<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jobs - FactoryTrack</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <header>
            <h1>Jobs Management</h1>
        </header>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Job Name</th>
                        <th>Customer</th>
                        <th>Total Bill</th>
                        <th>Received</th>
                        <th>Pending Balance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="jobsTable">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadJobs();
        });

        async function loadJobs() {
            const urlParams = new URLSearchParams(window.location.search);
            const customerFilter = urlParams.get('customer');
            
            const res = await fetch('api.php?action=get_jobs');
            const allJobs = await res.json();
            const tbody = document.getElementById('jobsTable');
            tbody.innerHTML = ''; // Clear existing
            
            // Filter if customer param exists
            const jobs = customerFilter 
                ? allJobs.filter(j => j.customer_name === customerFilter) 
                : allJobs;

            if (customerFilter) {
                 document.querySelector('h1').textContent = `Jobs for ${customerFilter}`;
            }
            
            jobs.forEach(job => {
                const tr = document.createElement('tr');
                const jobNameSafe = encodeURIComponent(job.job_name);
                const total = parseFloat(job.total_amount);
                const pending = parseFloat(job.pending_amount);
                const received = total - pending;
                
                tr.innerHTML = `
                    <td><strong>${job.job_name}</strong></td>
                    <td>${job.customer_name || '-'}</td>
                    <td>₹${total.toFixed(2)}</td>
                    <td style="color: green;">₹${received.toFixed(2)}</td>
                    <td style="color: ${pending > 0 ? 'red' : 'inherit'}; font-weight: bold;">₹${pending.toFixed(2)}</td>
                    <td style="display: flex; gap: 5px;">
                        ${pending > 0 ? `<button onclick="markJobPaid('${job.job_name}')" class="btn btn-sm btn-primary"><i class="fa-solid fa-check"></i> Mark Paid</button>` : `<span style="color: green; font-weight: bold; margin-right: 10px;"><i class="fa-solid fa-check-double"></i> Paid</span>`}
                        <a href="job_details.php?job_name=${jobNameSafe}" class="btn btn-sm btn-secondary"><i class="fa-solid fa-pen-to-square"></i> Details</a>
                        <a href="invoice.php?job_name=${jobNameSafe}" class="btn btn-sm btn-secondary" target="_blank"><i class="fa-solid fa-print"></i> Invoice</a>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        async function markJobPaid(jobName) {
            if (!confirm(`Mark ALL works under job "${jobName}" as Paid?`)) return;

            const formData = new FormData();
            formData.append('action', 'update_job_payment');
            formData.append('job_name', jobName);
            formData.append('status', 'Paid');

            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    loadJobs();
                } else {
                    alert('Failed to update status');
                }
            } catch (error) {
                alert('Error updating status');
            }
        }
    </script>
</body>
</html>
