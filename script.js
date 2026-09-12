document.addEventListener('DOMContentLoaded', () => {
    fetchWorks();
    fetchStats();
    fetchJobs();
    fetchSuggestions();

    // Toggle Scroll
    const toggleScrollBtn = document.getElementById('toggleScrollBtn');
    const worksTableContainer = document.getElementById('worksTableContainer');
    if (toggleScrollBtn && worksTableContainer) {
        toggleScrollBtn.addEventListener('click', () => {
            worksTableContainer.classList.toggle('scrollable-table');
        });
    }

    // Sort Dropdown
    const sortWorksBy = document.getElementById('sortWorksBy');
    if (sortWorksBy) {
        sortWorksBy.addEventListener('change', renderWorks);
    }

    // Toggle Custom Category Input & Options
    const categorySelect = document.getElementById('categorySelect');
    const customCategoryInput = document.getElementById('customCategoryInput');
    
    // Sub-options containers
    const drillOptions = document.getElementById('drillOptions');
    const latheOptions = document.getElementById('latheOptions');
    const partsOptions = document.getElementById('partsOptions');
    
    // Drill Inputs
    const drillType = document.getElementById('drillType');
    const drillCount = document.getElementById('drillCount');
    
    // Lathe Inputs
    const latheOp = document.getElementById('latheOp');

    // Parts Inputs
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
            
            // Drill Calc
            const price = parseFloat(drillType.value) || 0;
            const count = parseInt(drillCount.value) || 0;
            const total = price * count;
            amountInput.value = total.toFixed(2);
            
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
    
    // Drill Listeners
    drillType.addEventListener('change', updateCalculations);
    drillCount.addEventListener('input', updateCalculations);

    // Lathe Listeners
    latheOp.addEventListener('change', updateCalculations);

    // Parts Listeners
    partType.addEventListener('change', updateCalculations);
    boltSize.addEventListener('change', updateCalculations);
    partCount.addEventListener('input', updateCalculations);

    // Set Current Date Button
    document.getElementById('setDateBtn').addEventListener('click', () => {
        const date = new Date();
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        document.getElementById('workDateInput').value = `${year}-${month}-${day}`;
    });

    // Handle Form Submission
    document.getElementById('addWorkForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('action', 'add_work'); // Matches API check

        // Handle Custom Category
        if (categorySelect.value === 'Other') {
            formData.set('category', customCategoryInput.value);
        }

        try {
            const res = await fetch('api.php?action=add_work', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                e.target.reset();
                fetchWorks();
                fetchStats();
                fetchJobs();
                fetchSuggestions();
            } else {
                alert('Error submitting work');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });
});

async function fetchSuggestions() {
    try {
        const res = await fetch('api.php?action=get_suggestions');
        const data = await res.json();
        
        const customerList = document.getElementById('customerList');
        const jobList = document.getElementById('jobList');
        
        customerList.innerHTML = '';
        data.customers.forEach(name => {
            const option = document.createElement('option');
            option.value = name;
            customerList.appendChild(option);
        });

        jobList.innerHTML = '';
        data.jobs.forEach(name => {
            const option = document.createElement('option');
            option.value = name;
            jobList.appendChild(option);
        });
    } catch (error) {
        console.error('Failed to fetch suggestions');
    }
}

async function fetchJobs() {
    try {
        const res = await fetch('api.php?action=get_jobs');
        const jobs = await res.json();
        const tbody = document.getElementById('jobsTableBody');
        tbody.innerHTML = '';

        jobs.forEach(job => {
            const tr = document.createElement('tr');
            const jobNameSafe = encodeURIComponent(job.job_name);
            tr.innerHTML = `
                <td><strong>${job.job_name}</strong></td>
                <td>${job.customer_name || '-'}</td>
                <td>₹${parseFloat(job.pending_amount).toFixed(2)}</td>
                <td>
                    <a href="invoice.php?job_name=${jobNameSafe}" class="btn btn-sm btn-secondary" target="_blank">Detail Invoice</a>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (error) {
        console.error('Failed to fetch jobs');
    }
}

let allWorksData = [];

async function fetchWorks() {
    try {
        const res = await fetch('api.php?action=get_works');
        allWorksData = await res.json();
        renderWorks();
    } catch (error) {
        console.error('Failed to fetch works. Ensure DB is connected.');
    }
}

function renderWorks() {
    const sortVal = document.getElementById('sortWorksBy') ? document.getElementById('sortWorksBy').value : 'date_desc';
    let sortedWorks = [...allWorksData];
    
    sortedWorks.sort((a, b) => {
        if (sortVal === 'date_desc') return new Date(b.work_date) - new Date(a.work_date) || b.id - a.id;
        if (sortVal === 'date_asc') return new Date(a.work_date) - new Date(b.work_date) || a.id - b.id;
        if (sortVal === 'amount_desc') return parseFloat(b.amount) - parseFloat(a.amount);
        if (sortVal === 'amount_asc') return parseFloat(a.amount) - parseFloat(b.amount);
    });

    const tbody = document.getElementById('workTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';

    sortedWorks.forEach(work => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${work.work_date}</td>
            <td><strong>${work.job_name || '-'}</strong><br><small>${work.category}</small></td>
            <td>${work.description}</td>
            <td>₹${parseFloat(work.amount).toFixed(2)}</td>
            <td>
                <span class="badge ${work.payment_status === 'Paid' ? 'badge-paid' : 'badge-pending'}">
                    ${work.payment_status}
                </span>
            </td>
            <td>
                ${work.payment_status === 'Pending' 
                    ? `<button class="btn btn-sm btn-primary" onclick="markPaid(${work.id})">Mark Paid</button>` 
                    : '<span style="color: green;">✔</span>'}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function fetchStats() {
    try {
        const res = await fetch('api.php?action=get_stats');
        const stats = await res.json();
        
        document.getElementById('pendingAmount').textContent = '₹' + parseFloat(stats.total_pending).toFixed(2);
        document.getElementById('completedJobs').textContent = stats.completed_jobs;
    } catch (error) {
        console.error('Failed to fetch stats');
    }
}

async function markPaid(id) {
    if (!confirm('Mark this job as Paid?')) return;

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
            fetchWorks();
            fetchStats();
            fetchJobs();
        }
    } catch (error) {
        alert('Failed to update status');
    }
}
